<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use DateTimeInterface;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Throwable;

class ValidatesConventionValues
{
    /**
     * @throws FilterException|Throwable
     */
    public static function validateDate(mixed $value, string $column): void
    {
        if (is_array($value)) {
            static::validateDate($value[0], $column);
            static::validateDate($value[1], $column);

            return;
        }

        if ($value instanceof DateTimeInterface) {
            return;
        }

        throw_if(
            ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false),
            FilterException::invalidDate($column)
        );

        $date = date_parse($value);

        throw_if(
            ! checkdate($date['month'], $date['day'], $date['year']),
            FilterException::invalidDate($column)
        );
    }

    /**
     * @throws FilterException|Throwable
     */
    public static function validateNumber(mixed $value, string $column): void
    {
        if (is_array($value)) {
            static::validateNumber($value[0], $column);
            static::validateNumber($value[1], $column);

            return;
        }

        throw_if(
            ! is_numeric($value),
            FilterException::invalidNumber($column),
        );
    }

    /**
     * @throws FilterException|Throwable
     */
    public static function validateSortable(string $column, string $direction, array $sortable): void
    {
        throw_if(
            ! in_array($column, $sortable),
            FilterException::invalidSelectedSort($column),
        );

        throw_if(
            ! in_array($direction, ['asc', 'desc']),
            FilterException::invalidSortDirection($direction),
        );
    }
}
