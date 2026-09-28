<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Closure;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterResolverTrait;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
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
     * @throws Throwable
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_keys($this->pipes()),
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
                $parameters = $this->pipes()[$name];

                [$pipe, $identifier] = $this->resolvePipe($name);

                $this->filterInstanceHandler($pipe, $identifier);

                $pipe->authorizeResolved();

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
     * @return array{0: mixed, 1: string}
     *
     * @throws Throwable
     */
    private function resolvePipe(string $name): array
    {
        if (isset($this->quickFilters[$name])) {
            return [$this->quickFilters[$name], $name];
        }

        $pipeClass = $this->resolveFilter($name, $this->model);

        $this->notFoundFilterHandler($pipeClass);

        return [$this->getContainer()->make($pipeClass), $this->filterBasename($pipeClass)];
    }

    private function filterBasename(string $namespace): string
    {
        return class_basename($namespace);
    }

    /**
     * @throws Throwable
     */
    private function notFoundFilterHandler(string $filterClass): void
    {
        throw_if(
            ! class_exists($filterClass),
            FilterException::filterNotFound($this->filterBasename($filterClass)),
        );
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
