<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as IFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FilterFactory implements IFactory
{
    private $filterNamespace;

    public function factory(string $filter, Model $model): Filter
    {
        return self::make($filter, $model);
    }

    public function make(string $filterName, Model $model)
    {
        $this->setFilterNamespace($filterName, $model)->findFilter();

        $filter = app()->make($this->filterNamespace);

        if (!$filter instanceof Filter) {
            throw new InvalidArgumentException('The filter must be an instance of Filter.');
        }

        return $filter;
    }

    private function setFilterNamespace(string $filterName, Model $model)
    {
        $this->filterNamespace = config('eloquent-builder.namespace', 'App\\EloquentFilters\\').class_basename($model).'\\'.$this->resolveFilterName($filterName);

        return $this;
    }

    private function resolveFilterName(string $filterName)
    {
        return studly_case($filterName).'Filter';
    }

    private function findFilter()
    {
        throw_if(
            !class_exists($this->filterNamespace),
            NotFoundFilterException::class,
            'Filter not found.'
        );

        return true;
    }
}
