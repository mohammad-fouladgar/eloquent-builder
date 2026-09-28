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
        if (! $this->filters) {
            return $this->builder;
        }

        $this->apply($this->builder, $this->getFilters($this->filters));

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
