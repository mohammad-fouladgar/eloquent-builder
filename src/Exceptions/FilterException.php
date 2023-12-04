<?php

namespace Fouladgar\EloquentBuilder\Exceptions;

use Exception;

class FilterException extends Exception
{
    public static function filterInstance(string $filter): static
    {
        return new static("The `$filter` filter must be an instance of `Filter`.");
    }

    public static function filterNotFound(string $filter): static
    {
        return new static("Not found filter:  `$filter`.");
    }

    public static function invalidSelectedSort(string $column): static
    {
        return new static("The selected `$column` column is invalid.");
    }

    public static function invalidSortDirection(string $direction): static
    {
        return new static("The selected sort direction `$direction` is invalid.");
    }

    public static function invalidDate(string $column): static
    {
        return new static("The `$column` is not a valid date.");
    }

    public static function invalidNumber(string $column): static
    {
        return new static("The `$column` is not a valid number.");
    }
}
