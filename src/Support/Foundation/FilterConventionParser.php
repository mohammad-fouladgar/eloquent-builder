<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use Illuminate\Support\Str;

class FilterConventionParser
{
    public static function parseStringConvention(string $convention): array
    {
        if (!str_contains($convention, ':')) {
            return [];
        }

        [$operator, $value] = explode(':', $convention, 2);

        return [Str::studly(trim($operator)), trim($value)];
    }

    public static function parseStringValue(string $value): array|string
    {
        if (!str_contains($value, ',')) {
            return $value;
        }

        return array_map('trim', explode(',', $value));
    }
}
