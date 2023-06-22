<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Closure;
use Fouladgar\EloquentBuilder\Support\Foundation\AuthorizeWhenResolvedTrait;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter implements AuthorizeWhenResolved
{
    use AuthorizeWhenResolvedTrait;

    public function handle(Builder $builder, Closure $next, $value): Builder
    {
        return $next($this->apply($builder, $value));
    }

    /**
     * Apply the filter to a given Eloquent query builder.
     */
    abstract public function apply(Builder $builder, mixed $value): Builder;
}
