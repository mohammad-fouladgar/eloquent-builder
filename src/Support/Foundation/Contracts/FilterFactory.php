<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Illuminate\Database\Eloquent\Model;

interface FilterFactory
{
    /**
     * Create applied filter.
     *
     * @param string $filter
     * @param Model  $model
     *
     * @return Filter
     */
    public function factory(string $filter, Model $model): Filter;
}
