<?php

namespace Fouladgar\EloquentBuilder\Tests\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\SortableTrait;
use Fouladgar\EloquentBuilder\Exceptions\ValidateConventionException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class SortByFilter extends Filter
{
    use SortableTrait;

    protected array $sortable = [
        'birth_date', 'score',
    ];

    /**
     * @throws ValidateConventionException|Throwable
     */
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->applySort($builder, $value);
    }
}
