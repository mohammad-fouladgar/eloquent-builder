<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as Factory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FilterFactory implements Factory
{
    /**
     * Namespace of the model filter.
     *
     * @var string
     */
    protected $namespace = '';

    public function factory(string $filter, Model $model): Filter
    {
        return self::make($filter, $model);
    }

    protected function make(string $filter, Model $model): Filter
    {
        $this->setNamespace($filter, $model);

        if (!$this->filterExists($filter)) {
            $this->notFoundFilter();
        }

        $filter = app($this->namespace);

        if (!$filter instanceof Filter) {
            $this->failedImplementation();
        }

        return $filter;
    }

    /**
     * Handle a not found filter.
     *
     * @throws NotFoundFilterException
     */
    protected function notFoundFilter()
    {
        throw new NotFoundFilterException('Not found the filter: '.$this->filterBasename());
    }

    /**
     * Handle a failed implementation filter.
     *
     * @throws \InvalidArgumentException
     */
    protected function failedImplementation()
    {
        throw new InvalidArgumentException('The '.$this->filterBasename().' filter must be an instance of Filter.');
    }

    /**
     * Check a filter exists.
     *
     * @return bool
     */
    protected function filterExists(): bool
    {
        return class_exists($this->namespace);
    }

    private function setNamespace(string $filter, Model $model)
    {
        $config = config('eloquent-builder.namespace', 'App\\EloquentFilters\\');

        $this->namespace = $config.class_basename($model).'\\'.$this->resolveFilterName($filter);
    }

    private function resolveFilterName(string $filter): string
    {
        return studly_case($filter).'Filter';
    }

    private function filterBasename(): string
    {
        return class_basename($this->namespace);
    }
}
