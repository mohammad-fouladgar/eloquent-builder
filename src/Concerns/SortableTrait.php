<?php

namespace Fouladgar\EloquentBuilder\Concerns;

use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterConventionParser;
use Fouladgar\EloquentBuilder\Support\Foundation\ValidatesConventionValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

trait SortableTrait
{
    /**
     * @throws FilterException|Throwable
     */
    protected function applySort(Builder $builder, mixed $value): Builder
    {
        Arr::isList($value) ?
            $this->sortByConventionValues($builder, $value) :
            $this->sortByAssociatedValues($builder, $value);

        return $builder;
    }

    /**
     * @throws FilterException|Throwable
     */
    protected function sortByConventionValues(Builder $builder, mixed $value): void
    {
        foreach ($value as $convention) {
            $parsedConvention = FilterConventionParser::parseStringConvention($convention);
            if (! count($parsedConvention)) {
                $direction = 'asc';
                $column = $convention;
            } else {
                [$column, $direction] = $parsedConvention;
            }

            $this->prepareSort($builder, $column, $direction);
        }
    }

    /**
     * @throws FilterException|Throwable
     */
    protected function sortByAssociatedValues(Builder $builder, mixed $value): void
    {
        foreach ($value as $column => $direction) {
            $this->prepareSort($builder, $column, $direction);
        }
    }

    /**
     * @throws FilterException|Throwable
     */
    protected function prepareSort(Builder $builder, string $column, string $direction): void
    {
        $column = Str::lower(Str::snake($column));
        $direction = Str::lower($direction);
        ValidatesConventionValues::validateSortable($column, $direction, $this->sortable ?? []);

        $builder->orderBy($column, $direction);
    }
}
