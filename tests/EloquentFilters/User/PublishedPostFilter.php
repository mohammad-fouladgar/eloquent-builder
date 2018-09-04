<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFilter;
use Illuminate\Database\Eloquent\Builder;

class PublishedPostFilter implements IFilter
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
        return $builder->where(function ($query) use ($value) {
            $query->whereHas('posts', function ($query) use ($value) {
                $query->where('is_published', $value);
            });
        });
    }
}
