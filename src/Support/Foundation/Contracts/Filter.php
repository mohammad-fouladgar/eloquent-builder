<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Fouladgar\EloquentBuilder\Support\Foundation\AuthorizeWhenResolvedTrait;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter implements AuthorizeWhenResolved
{
    use AuthorizeWhenResolvedTrait;

    /**
     * Apply the filter to a given Eloquent query builder.
     */
    abstract public function apply(Builder $builder, mixed $value): Builder;
}
