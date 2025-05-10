<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\FiltersDatesTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthenticatedAtFilter extends Filter
{
    use FiltersDatesTrait;

    /**
     * @throws ValidationException|Throwable
     */
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->filterDate($builder, $value, 'authenticated_at');
    }
}
