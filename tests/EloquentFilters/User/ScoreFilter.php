<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\FiltersNumbersTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Throwable;

class ScoreFilter extends Filter
{
    use FiltersNumbersTrait;

    /**
     * @throws ValidationException|Throwable
     */
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->filterNumber($builder, $value, 'score');
    }
}
