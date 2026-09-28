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

    /**
     * Filter soft-deletable models by trashed state: `with`, `only`, or anything else (default, excludes trashed).
     */
    public static function trashed(string $name = 'trashed'): static
    {
        return new static($name, static fn (Builder $builder, mixed $value): Builder => match ($value) {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => $builder,
        });
    }

    /**
     * Eager-load relations from a comma-separated string or array, restricted to the given whitelist.
     *
     * @param  string[]  $allowed
     */
    public static function includes(string $name = 'include', array $allowed = []): static
    {
        return new static($name, static function (Builder $builder, mixed $value) use ($allowed): Builder {
            $relations = array_intersect(self::parseList($value), $allowed);

            return $builder->with(array_values($relations));
        });
    }

    /**
     * Restrict the selected columns on the root model to a comma-separated string or array,
     * intersected with the given whitelist. The primary key is always selected, since relations
     * and model identity depend on it.
     *
     * @param  string[]  $allowed
     */
    public static function fields(string $name = 'fields', array $allowed = []): static
    {
        return new static($name, static function (Builder $builder, mixed $value) use ($allowed): Builder {
            $columns = array_intersect(self::parseList($value), $allowed);

            return $builder->select(array_unique([
                $builder->getModel()->getKeyName(),
                ...$columns,
            ]));
        });
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(Builder $builder, mixed $value): Builder
    {
        return ($this->handler)($builder, $value) ?? $builder;
    }

    /**
     * Normalize a comma-separated string or array into a trimmed, non-empty string list.
     *
     * @return string[]
     */
    private static function parseList(mixed $value): array
    {
        $items = array_filter(is_array($value) ? $value : explode(',', (string) $value), is_scalar(...));

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        ), static fn (string $item): bool => $item !== ''));
    }
}
