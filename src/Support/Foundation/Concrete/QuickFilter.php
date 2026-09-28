<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Closure;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class QuickFilter extends Filter
{
    private function __construct(
        private readonly string $name,
        private readonly Closure $handler,
    ) {}

    /**
     * Filter using a strict `=` match on the given column.
     */
    public static function exact(string $name, ?string $column = null): static
    {
        $column ??= $name;

        return new static(
            $name,
            static fn (Builder $builder, mixed $value): Builder => $builder->where($column, $value)
        );
    }

    /**
     * Filter using a `LIKE %value%` match on the given column.
     */
    public static function partial(string $name, ?string $column = null): static
    {
        $column ??= $name;

        return new static(
            $name,
            static fn (Builder $builder, mixed $value): Builder => $builder->where($column, 'like', '%'.$value.'%')
        );
    }

    /**
     * Filter by invoking an existing local scope on the model.
     */
    public static function scope(string $name, ?string $scope = null): static
    {
        $scope ??= $name;

        return new static(
            $name,
            static fn (Builder $builder, mixed $value): Builder => $builder->{$scope}($value)
        );
    }

    /**
     * Filter using a custom callback.
     */
    public static function callback(string $name, Closure $callback): static
    {
        return new static($name, $callback);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(Builder $builder, mixed $value): Builder
    {
        return ($this->handler)($builder, $value) ?? $builder;
    }
}
