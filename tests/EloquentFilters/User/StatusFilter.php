<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter extends Filter
{
    /**
     * Determine if the user is authorized to make this filter.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Apply the status condition to the query.
     *
     * @param Builder $builder
     * @param mixed   $value
     *
     * @return Builder
     */
    public function apply(Builder $builder, $value): Builder
    {
        return $builder->where('status', $value);
    }
}
