<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory as FilterFactoryConcrete;
use Illuminate\Database\Eloquent\Model;

interface FilterFactory
{
    /**
     * Create applied filter.
     *
     * @param string $filter
     * @param Model  $model
     *
     * @throws NotFoundFilterException
     *
     * @return Filter
     */
    public function make(string $filter, Model $model): Filter;

    /**
     * @param string $namespace
     *
     * @return FilterFactoryConcrete
     */
    public function setCustomNamespace(string $namespace = ''): FilterFactoryConcrete;

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void;

    /**
     * @return string
     */
    public function getCustomNamespace(): string;
}
