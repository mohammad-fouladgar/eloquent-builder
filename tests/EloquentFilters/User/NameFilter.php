<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class NameFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('name', 'like', "%{$value}%");
    }
}
