<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Illuminate\Database\Eloquent\Builder;

class InvalidImplementedFilter
{
    /**
     * Undocumented function.
     *
     * @param Builder $builder
     * @param mixed   $value
     *
     * @return Builder
     */
    public function apply(Builder $builder, $value): Builder
    {
        return $builder;
    }
}
