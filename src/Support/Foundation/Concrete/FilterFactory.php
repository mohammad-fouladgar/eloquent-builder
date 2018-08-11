<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Illuminate\Database\Eloquent\Model;
use Fouladgar\EloquentBuilder\Exception\FilterNotFound;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as IFactory;

class FilterFactory implements IFactory
{
    public static function factory(string $filter, Model $model): Filter
    {
        return static::makeFilter($filter, $model);
    }

    private static function isValidFilter(string $filter): bool
    {
        return class_exists($filter);
    }

    private static function makeFilter(string $filterName, Model $model)
    {
        $filter = static::resolveNameSpace($model).static::resolveFilterName($filterName);

        if (static::isValidFilter($filter)) {
            return app($filter);
        }

        throw new FilterNotFound("The {$filterName} filter not found for the ".static::getClassName($model).' model.');
    }

    /**
     * Undocumented function.
     *
     * @param string $filterName
     *
     * @return string
     */
    private static function resolveFilterName(string $filterName): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $filterName))).'Filter';
    }

    /**
     * Undocumented function.
     *
     * @param Model $model
     *
     * @return string
     */
    private static function resolveNameSpace(Model $model): string
    {
        return config('eloquent-builder.namespace', 'App\\EloquentFilters\\').static::getClassName($model).'\\';
    }

    /**
     * Undocumented function.
     *
     * @param Model $model
     *
     * @return string
     */
    private static function getClassName(Model $model): string
    {
        return class_basename(get_class($model));
    }
}
