<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class EloquentBuilder
{
    /**
     * the filter factory.
     *
     * @var $filterFactory
     */
    protected $filterFactory;

    /**
     * Custom filters namespace
     *
     * @var string
     */
    protected $filterNamespace = '';

    /**
     * EloquentBuilder constructor.
     *
     * @param FilterFactory $filterFactory
     */
    public function __construct(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Create a new EloquentBuilder for a request and model.
     *
     * @param string|Builder $query Model class or eloquent builder
     * @param array $filters
     *
     * @return Builder
     * @throws Exceptions\NotFoundFilterException
     */
    public function to($query, array $filters = null): Builder
    {
        /** @var Builder $query */
        $query = $this->resolveQuery($query);

        if (!$filters) {
            return $query;
        }

        $this->applyFilters($query, $this->getFilters($filters));

        return $query;
    }

    /**
     * Set custom filters namespace
     *
     * @param string $namespace
     * @return EloquentBuilder
     */
    public function setFilterNamespace(string $namespace = ''): EloquentBuilder
    {
        $this->filterNamespace = $namespace;

        return $this;
    }

    /**
     * Resolve the incoming query to Builder
     *
     * @param string/EloquentModel/Builder $query
     * @return Builder
     */
    private function resolveQuery($query): Builder
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
     *
     * @param array $filters
     * @return array
     */
    private function getFilters(array $filters = []): array
    {
        return collect($filters)->getFilters();
    }

    /**
     * Apply filters to Query Builder.
     *
     * @param Builder $query
     * @param array $filters
     *
     * @return Builder
     * @throws Exceptions\NotFoundFilterException
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter => $value) {
            $query = $this->filterFactory->setCustomNamespace($this->filterNamespace)
                                         ->make($filter, $query->getModel())
                                         ->apply($query, $value);
        }

        return $query;
    }
}
