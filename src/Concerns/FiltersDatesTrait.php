<?php

namespace Fouladgar\EloquentBuilder\Concerns;

use BadMethodCallException;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterConventionParser;
use Fouladgar\EloquentBuilder\Support\Foundation\ValidatesConventionValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Throwable;

trait FiltersDatesTrait
{
    /**
     * @throws ValidationException|BadMethodCallException|Throwable
     */
    public function filterDate(Builder $builder, mixed $value, string $column): Builder
    {
        $parsedConvention = is_array($value) ?
            ['Between', implode(',', $value)] :
            FilterConventionParser::parseStringConvention($value);

        if (! count($parsedConvention)) {
            return $this->applyDefaultFilter($builder, $value, $column);
        }

        [$operator, $date] = $parsedConvention;

        $date = FilterConventionParser::parseStringValue($date);

        ValidatesConventionValues::validateDate($date, $column);

        $method = "filter{$operator}Date";

        return $this->$method($builder, $date, $column);
    }

    /**
     * @throws Throwable|BadMethodCallException
     */
    public function __call(string $method, array $arguments)
    {
        throw_if(
            ! method_exists(static::class, $method),
            BadMethodCallException::class,
            sprintf('Method %s::%s does not exist.', static::class, $method)
        );
    }

    protected function filterBetweenDate(Builder $builder, array $dates, string $column): Builder
    {
        return $builder->whereBetween($column, $dates);
    }

    protected function filterAfterDate(Builder $builder, string $date, string $column): Builder
    {
        return $builder->where($column, ">", $date);
    }

    protected function filterAfterOrEqualDate(Builder $builder, string $date, string $column): Builder
    {
        return $builder->where($column, ">=", $date);
    }

    protected function filterBeforeDate(Builder $builder, string $date, string $column): Builder
    {
        return $builder->where($column, "<", $date);
    }

    protected function filterBeforeOrEqualDate(Builder $builder, string $date, string $column): Builder
    {
        return $builder->where($column, "<=", $date);
    }

    protected function filterEqualsDate(Builder $builder, string $date, string $column): Builder
    {
        return $builder->where($column, $date);
    }

    protected function filterSameDate(Builder $builder, string $date, string $column): Builder
    {
        return $this->filterEqualsDate($builder, $date, $column);
    }

    /**
     * @throws FilterException|Throwable
     */
    private function applyDefaultFilter(Builder $builder, mixed $value, string $column): Builder
    {
        $parsedValue = FilterConventionParser::parseStringValue($value);

        ValidatesConventionValues::validateDate($parsedValue, $column);

        return is_array($parsedValue) ?
            $this->filterBetweenDate($builder, $parsedValue, $column) :
            $this->filterEqualsDate($builder, $parsedValue, $column);
    }
}
