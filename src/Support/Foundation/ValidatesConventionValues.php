<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use DateTimeInterface;
use Fouladgar\EloquentBuilder\Exceptions\ValidateConventionException;
use Throwable;

class ValidatesConventionValues
{
    private static string $exception = ValidateConventionException::class;

    /**
     * @throws ValidateConventionException|Throwable
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

        $exceptionMessage = sprintf("The %s is not a valid date.", $column);

        throw_if(
            ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false),
            static::$exception,
            $exceptionMessage
        );

        $date = date_parse($value);

        throw_if(
            ! checkdate($date['month'], $date['day'], $date['year']),
            static::$exception,
            $exceptionMessage
        );
    }

    /**
     * @throws ValidateConventionException|Throwable
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
            static::$exception,
            sprintf("The %s is not a valid number.", $column)
        );
    }

    /**
     * @throws ValidateConventionException|Throwable
     */
    public static function validateSortable(string $column, string $direction, array $sortable): void
    {
        throw_if(
            ! in_array($column, $sortable),
            static::$exception,
            sprintf("The selected %s column is invalid", $column)
        );

        throw_if(
            ! in_array($direction, ['asc', 'desc']),
            static::$exception,
            'The selected sort direction is invalid'
        );
    }
}
