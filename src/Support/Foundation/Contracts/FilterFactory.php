<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory as FilterFactoryConcrete;
use Illuminate\Database\Eloquent\Model;

interface FilterFactory
{
    /**
     * @throws NotFoundFilterException
     */
    public function make(string $filter, Model $model): Filter;

    public function setCustomNamespace(string $namespace = ''): FilterFactoryConcrete;
}
