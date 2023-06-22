<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\Pipeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Throwable;

class EloquentBuilder
{
    protected string $filterNamespace = '';

    private ?array $filters = null;

    private string|null|Builder|EloquentModel $builder = null;

    public function __construct(protected Pipeline $pipeline)
    {
    }

    public function filters(array $filters = null): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function filter(array $filters): static
    {
        $this->filters += $filters;

        return $this;
    }

    public function model(string|EloquentModel|Builder $builder): static
    {
        $this->builder = $this->resolveQuery($builder);

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
        if (!$this->filters) {
            return $this->builder;
        }

        $this->apply($this->builder, $this->getFilters($this->filters));

        return $this->builder;
    }

    /**
     * @throws FilterException|Throwable
     * @deprecated Please use the `thenApply` method instead of this.
     */
    public function to(string|EloquentModel|Builder $query = null, array $filters = null): Builder
    {
        /** @var Builder $query */
        $query = $this->builder ?: $this->resolveQuery($query);

        $filters = $this->filters ?: $filters;

        if (! $filters) {
            return $query;
        }

        $this->apply($query, $this->getFilters($filters));

        return $query;
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
            ->through($filters)
            ->thenReturn();
    }
}
