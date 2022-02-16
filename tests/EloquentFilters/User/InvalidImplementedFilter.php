<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Illuminate\Database\Eloquent\Builder;

class InvalidImplementedFilter
{
    public function apply(Builder $builder, $value): Builder
    {
        return $builder;
    }
}
