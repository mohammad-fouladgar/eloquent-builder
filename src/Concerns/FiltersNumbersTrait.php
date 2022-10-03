<?php

namespace Fouladgar\EloquentBuilder\Concerns;

use BadMethodCallException;
use Fouladgar\EloquentBuilder\Exceptions\ValidateConventionException;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterConventionParser;
use Fouladgar\EloquentBuilder\Support\Foundation\ValidatesConventionValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Throwable;

trait FiltersNumbersTrait
{
    /**
     * @throws ValidationException|BadMethodCallException|Throwable
     */
    public function filterNumber(Builder $builder, mixed $value, string $column): Builder
    {
        $parsedConvention = is_array($value) ?
            ['Between', implode(',', $value)] :
            FilterConventionParser::parseStringConvention($value);

        if (!count($parsedConvention)) {
            return $this->applyDefaultFilter($builder, $value, $column);
        }

        [$operator, $number] = $parsedConvention;

        $number = FilterConventionParser::parseStringValue($number);

        ValidatesConventionValues::validateNumber($number, $column);

        $method = "filter{$operator}Number";

        throw_if(
            !method_exists(static::class, $method),
            BadMethodCallException::class,
            sprintf('Method %s::%s does not exist.', static::class, $method)
        );

        return $this->$method($builder, $number, $column);
    }

    protected function filterBetweenNumber(Builder $builder, array $numbers, string $column): Builder
    {
        return $builder->whereBetween($column, $numbers);
    }

    protected function filterGtNumber(Builder $builder, string $number, string $column): Builder
    {
        return $builder->where($column, ">", $number);
    }

    protected function filterGteNumber(Builder $builder, string $number, string $column): Builder
    {
        return $builder->where($column, ">=", $number);
    }

    protected function filterLtNumber(Builder $builder, string $number, string $column): Builder
    {
        return $builder->where($column, "<", $number);
    }

    protected function filterLteNumber(Builder $builder, string $number, string $column): Builder
    {
        return $builder->where($column, "<=", $number);
    }

    protected function filterEqualsNumber(Builder $builder, string $number, string $column): Builder
    {
        return $builder->where($column, $number);
    }

    /**
     * @throws ValidateConventionException|Throwable
     */
    private function applyDefaultFilter(Builder $builder, mixed $value, string $column): Builder
    {
        $parsedValue = FilterConventionParser::parseStringValue($value);

        ValidatesConventionValues::validateNumber($parsedValue, $column);

        return is_array($parsedValue) ?
            $this->filterBetweenNumber($builder, $parsedValue, $column) :
            $this->filterEqualsNumber($builder, $parsedValue, $column);
    }
}
