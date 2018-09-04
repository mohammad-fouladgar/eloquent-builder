<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFactory;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFilter;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FilterFactory implements IFactory
{
    private $filterNamespace;

    public function factory(string $filter, Model $model): IFilter
    {
        return self::make($filter, $model);
    }

    public function make(string $filterName, Model $model): IFilter
    {
        $this->setFilterNamespace($filterName, $model)->findFilter();

        $filter = app()->make($this->filterNamespace);

        if (!$filter instanceof IFilter) {
            throw new InvalidArgumentException('The filter must be an instance of IFilter.');
        }

        return $filter;
    }

    private function setFilterNamespace(string $filterName, Model $model)
    {
        $this->filterNamespace = config('eloquent-builder.namespace', 'App\\EloquentFilters\\').class_basename($model).'\\'.$this->resolveFilterName($filterName);

        return $this;
    }

    private function resolveFilterName(string $filterName): string
    {
        return studly_case($filterName).'Filter';
    }

    private function findFilter(): bool
    {
        throw_if(
            !class_exists($this->filterNamespace),
            NotFoundFilterException::class,
            'Filter not found.'
        );

        return true;
    }
}
