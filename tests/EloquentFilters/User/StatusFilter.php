<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter extends Filter
{
    public function authorize(): bool
    {
        return false;
    }

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('status', $value);
    }
}
