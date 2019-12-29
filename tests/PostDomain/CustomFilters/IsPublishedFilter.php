<?php

namespace Fouladgar\EloquentBuilder\Tests\PostDomain\CustomFilters;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class IsPublishedFilter extends Filter
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
        return $builder->where('is_published', '=', $value);
    }
}
