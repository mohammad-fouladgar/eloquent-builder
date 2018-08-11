<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Exception\FilterNotFound;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentBuilder
{
    /**
     * Create a new EloquentBuilder for a request and model.
     *
     * @param string|Builder $query   Model class or eloquent builder
     * @param array          $filters
     *
     * @return
     */
    public static function to($query, array $filters = null): Builder
    {
        if (is_string($query)) {
            $query = ($query)::query();
        }

        if (!$filters) {
            return $query;
        }

        static::build($query, $filters);

        return $query;
    }

    /**
     * Undocumented function.
     *
     * @param Builder $query
     * @param array   $filters
     */
    private static function build(Builder &$query, array $filters)
    {
        foreach ($filters as $filterName => $value) {
            try {
                $query = FilterFactory::factory($filterName, $query->getModel())
                                        ->apply($query, $value);
            } catch (FilterNotFound $e) {
                \Log::warning($e->getMessage());
            }
        }
    }
}
