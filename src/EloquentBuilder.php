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
     * @var
     */
    protected $filterFactory;

    /**
     * EloquentBuilder constructor.
     */
    public function __construct(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Create a new EloquentBuilder for a request and model.
     *
     * @param string/EloquentModel/Builder  $query  Model class,Eloquent model instance or Eloquent builder
     * @param array                         $filters
     */
    public function to($query, array $filters = null): Builder
    {
        /** @var Builder $query */
        $query = $this->resolveQuery($query);

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
     * Resolve the incoming query to Builder
     *
     * @param string/EloquentModel/Builder $query
     * @return void
     */
    private function resolveQuery($query):Builder
    {
        if (is_string($query)) {
           return  $query::query();
        }

        if($query instanceof EloquentModel){
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
     * Apply filters to Query Builder.
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter => $value) {
            $query = $this->filterFactory->make($filter, $query->getModel())
                                         ->apply($query, $value);
        }

        return $query;
    }
}
