<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class PublishedPostFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where(function ($query) use ($value) {
            $query->whereHas('posts', function ($query) use ($value) {
                $query->where('is_published', $value);
            });
        });
    }
}
