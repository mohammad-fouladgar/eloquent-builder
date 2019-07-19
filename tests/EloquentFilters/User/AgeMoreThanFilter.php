<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter extends Filter
{
    public function authorize()
    {
        return true;
    }

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
