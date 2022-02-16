<?php

namespace Fouladgar\EloquentBuilder\Tests\UserDomain\CustomFilters;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter extends Filter
{
    public function authorize(): bool
    {
        return true;
    }

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('age', '>', $value);
    }
}
