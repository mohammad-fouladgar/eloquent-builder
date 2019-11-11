<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory;
use Illuminate\Database\Eloquent\Builder;

class EloquentBuilder
{
    /**
     * the filter factory.
     *
     * @var
     */
    protected $filterFactory;

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
     * @param string|Builder $query   Model class or eloquent builder
     * @param array          $filters
     *
     * @return Builder
     */
    public function to($query, array $filters = null): Builder
    {
        if (is_string($query)) {
            $query = $query::query();
        }

        if (!$filters) {
            return $query;
        }

        $this->applyFilters(
            $query,
            $this->getFilters($filters)
        );

        return $query;
    }

    /**
     * Returns only filters that have value.
     *
     * @param array $filters
     *
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
     * @param array   $filters
     *
     * @return Builder
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter => $value) {
            $query = $this->filterFactory
                ->factory($filter, $query->getModel())
                ->apply($query, $value);
        }

        return $query;
    }
}
