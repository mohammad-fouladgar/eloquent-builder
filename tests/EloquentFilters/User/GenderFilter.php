<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class GenderFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('gender', $value);
    }
}
