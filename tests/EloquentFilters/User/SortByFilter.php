<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Illuminate\Database\Eloquent\Builder;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;

class SortByFilter implements Filter
{
    /**
     * Undocumented function.
     *
     * @param Builder $builder
     * @param mixed   $sortBy
     *
     * @return Builder
     */
    public function apply(Builder $builder, $sortBy): Builder
    {
        return $builder->orderByDesc($sortBy);
    }
}
