<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Illuminate\Database\Eloquent\Builder;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;

class AgeFilter implements Filter
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
        return $builder->where('age', '>', $value);
    }
}
