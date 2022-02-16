<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class EloquentBuilder
{
    protected string $filterNamespace = '';

    public function __construct(protected FilterFactory $filterFactory)
    {
    }

    /**
     * @throws Exceptions\NotFoundFilterException
     */
    public function to(string|EloquentModel|Builder $query, array $filters = null): Builder
    {
        /** @var Builder $query */
        $query = $this->resolveQuery($query);

        if (!$filters) {
            return $query;
        }

        $this->applyFilters($query, $this->getFilters($filters));

        return $query;
    }

    public function setFilterNamespace(string $namespace = ''): self
    {
        $this->filterNamespace = $namespace;

        return $this;
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
     * @throws Exceptions\NotFoundFilterException
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filter => $value) {
            $query = $this->filterFactory->setCustomNamespace($this->filterNamespace)
                                         ->make($filter, $query->getModel())
                                         ->apply($query, $value);
        }
    }
}
