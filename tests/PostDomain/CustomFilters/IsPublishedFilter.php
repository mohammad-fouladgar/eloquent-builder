<?php

namespace Fouladgar\EloquentBuilder\Tests\PostDomain\CustomFilters;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class IsPublishedFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('is_published', '=', $value);
    }
}
