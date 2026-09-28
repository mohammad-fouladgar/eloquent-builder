<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

class FilterGroup
{
    private function __construct(private readonly array $keys) {}

    /**
     * Combine the given filter keys with `OR` instead of the default `AND`.
     */
    public static function or(array $keys): static
    {
        return new static($keys);
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return $this->keys;
    }
}
