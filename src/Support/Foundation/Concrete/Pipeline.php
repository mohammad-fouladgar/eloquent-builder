<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Closure;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterResolverTrait;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use Throwable;

class Pipeline extends BasePipeline
{
    use FilterResolverTrait;

    protected string $customNamespace = '';

    private Model $model;

    /**
     * @var array<string, Filter>
     */
    private array $quickFilters = [];

    /**
     * @var FilterGroup[]
     */
    private array $filterGroups = [];

    public function __construct(protected ConfigRepository $config, ?Container $container = null)
    {
        parent::__construct($container);
    }

    public function model($model): static
    {
        $this->model = $model;

        return $this;
    }

    public function customNamespace(string $namespace = ''): static
    {
        $this->customNamespace = $namespace;

        return $this;
    }

    /**
     * @param  array<string, Filter>  $quickFilters
     */
    public function quickFilters(array $quickFilters): static
    {
        $this->quickFilters = $quickFilters;

        return $this;
    }

    /**
     * @param  FilterGroup[]  $filterGroups
     */
    public function filterGroups(array $filterGroups): static
    {
        $this->filterGroups = $filterGroups;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function then(Closure $destination)
    {
        $this->applyFilterGroups();

        $pipeline = array_reduce(
            array_keys($this->ungroupedFilters()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    /**
     * @throws Throwable
     */
    protected function carry(): Closure
    {
        return fn ($stack, $name) => function ($passable) use ($stack, $name) {
            try {
                $resolved = $this->resolveAndAuthorize($name);

                if ($resolved === null) {
                    return $this->handleCarry($stack($passable));
                }

                [$pipe] = $resolved;
                $parameters = $this->pipes()[$name];

                $carry = method_exists($pipe, $this->method)
                    ? $pipe->{$this->method}($passable, $stack, $parameters)
                    : $pipe($passable, $stack, $parameters);

                return $this->handleCarry($carry);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * @throws Throwable
     */
    private function applyFilterGroups(): void
    {
        $filters = $this->pipes();

        foreach ($this->filterGroups as $group) {
            $groupFilters = array_intersect_key($filters, array_flip($group->keys()));

            if ($groupFilters === []) {
                continue;
            }

            $this->passable->where(function (Builder $query) use ($groupFilters) {
                foreach ($groupFilters as $name => $value) {
                    $query->orWhere(fn (Builder $nested) => $this->applyGroupedFilter($nested, $name, $value));
                }
            });
        }
    }

    /**
     * @throws Throwable
     */
    private function applyGroupedFilter(Builder $builder, string $name, mixed $value): Builder
    {
        $resolved = $this->resolveAndAuthorize($name);

        if ($resolved === null) {
            return $builder;
        }

        [$pipe] = $resolved;

        return $pipe->apply($builder, $value);
    }

    private function ungroupedFilters(): array
    {
        $groupedKeys = array_merge([], ...array_map(
            static fn (FilterGroup $group): array => $group->keys(),
            $this->filterGroups
        ));

        return array_diff_key($this->pipes(), array_flip($groupedKeys));
    }

    /**
     * @return array{0: Filter, 1: string}|null
     *
     * @throws Throwable
     */
    private function resolveAndAuthorize(string $name): ?array
    {
        $resolved = $this->resolvePipe($name);

        if ($resolved === null) {
            return null;
        }

        [$pipe, $identifier] = $resolved;

        $this->filterInstanceHandler($pipe, $identifier);

        $pipe->authorizeResolved();

        return [$pipe, $identifier];
    }

    /**
     * @return array{0: mixed, 1: string}|null
     *
     * @throws Throwable
     */
    private function resolvePipe(string $name): ?array
    {
        if (isset($this->quickFilters[$name])) {
            return [$this->quickFilters[$name], $name];
        }

        $pipeClass = $this->resolveFilter($name, $this->model);

        if ($this->missingFilterHandler($pipeClass)) {
            return null;
        }

        return [$this->getContainer()->make($pipeClass), $this->filterBasename($pipeClass)];
    }

    private function filterBasename(string $namespace): string
    {
        return class_basename($namespace);
    }

    /**
     * @return bool whether the filter is missing and should be silently skipped
     *
     * @throws Throwable
     */
    private function missingFilterHandler(string $filterClass): bool
    {
        if (class_exists($filterClass)) {
            return false;
        }

        throw_if(
            ! $this->ignoresMissingFilters(),
            FilterException::filterNotFound($this->filterBasename($filterClass)),
        );

        return true;
    }

    private function ignoresMissingFilters(): bool
    {
        return (bool) $this->config->get('eloquent-builder.ignore_missing_filters', false);
    }

    /**
     * @throws Throwable
     */
    private function filterInstanceHandler(mixed $pipe, string $identifier): void
    {
        throw_if(
            ! $pipe instanceof Filter,
            FilterException::filterInstance($identifier)
        );
    }
}
