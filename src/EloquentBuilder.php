<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterGroup;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\Pipeline;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\QuickFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Throwable;

class EloquentBuilder
{
    protected string $filterNamespace = '';

    private array $filters = [];

    private array $quickFilters = [];

    /**
     * @var FilterGroup[]
     */
    private array $filterGroups = [];

    private array $defaults = [];

    /**
     * @var array<string, array<int, mixed>>
     */
    private array $ignoredValues = [];

    private string|null|Builder|EloquentModel $builder = null;

    public function __construct(protected Pipeline $pipeline) {}

    public function filters(array $filters = []): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function filter(array $filters): static
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    public function model(string|EloquentModel|Builder $builder): static
    {
        $this->builder = $this->resolveQuery($builder);

        return $this;
    }

    /**
     * @param  QuickFilter[]  $quickFilters
     */
    public function quickFilters(array $quickFilters): static
    {
        $this->quickFilters = collect($quickFilters)->keyBy(
            static fn (QuickFilter $quickFilter): string => $quickFilter->getName()
        )->all();

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
     * Fill in a value for any filter key that has no value provided.
     */
    public function defaults(array $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Treat the given values as if the filter was never provided, e.g. `['status' => ['all']]`.
     *
     * @param  array<string, array<int, mixed>>  $ignoredValues
     */
    public function ignoreValues(array $ignoredValues): static
    {
        $this->ignoredValues = $ignoredValues;

        return $this;
    }

    public function setFilterNamespace(string $namespace = ''): self
    {
        $this->filterNamespace = $namespace;

        return $this;
    }

    /**
     * @throws FilterException|Throwable
     */
    public function thenApply(): Builder
    {
        $filters = $this->resolveFilters();

        if ($filters === []) {
            return $this->builder;
        }

        $this->apply($this->builder, $filters);

        return $this->builder;
    }

    private function resolveQuery(string|EloquentModel|Builder $query): Builder
    {
        if (is_string($query)) {
            return $query::query();
        }

        if ($query instanceof EloquentModel) {
            return $query->query();
        }

        return $query;
    }

    /**
     * Returns only filters that have value.
     */
    private function getFilters(array $filters = []): array
    {
        return collect($filters)->getFilters();
    }

    /**
     * Merge in defaults for any filter left without a value, after stripping ignored values.
     */
    private function resolveFilters(): array
    {
        $filters = $this->getFilters($this->withoutIgnoredValues($this->filters));

        return $this->getFilters($filters + $this->defaults);
    }

    private function withoutIgnoredValues(array $filters): array
    {
        foreach ($this->ignoredValues as $key => $ignored) {
            if (! array_key_exists($key, $filters) || ! is_scalar($filters[$key])) {
                continue;
            }

            $ignored = array_map(strval(...), array_filter((array) $ignored, is_scalar(...)));

            if (in_array((string) $filters[$key], $ignored, true)) {
                unset($filters[$key]);
            }
        }

        return $filters;
    }

    /**
     * @throws FilterException|Throwable
     */
    private function apply(Builder $builder, array $filters): void
    {
        $this->pipeline
            ->send($builder)
            ->model($builder->getModel())
            ->customNamespace($this->filterNamespace)
            ->quickFilters($this->quickFilters)
            ->filterGroups($this->filterGroups)
            ->through($filters)
            ->thenReturn();
    }
}
