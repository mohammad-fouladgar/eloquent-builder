<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait FilterResolverTrait
{
    private function resolveFilter(string $filter, Model $model): string
    {
        return $this->sanitizeNamespace($this->resolveNamespace($filter, $model));
    }

    private function resolveNamespace(string $filter, Model $model): string
    {
        if ($custom = $this->customNamespace) {
            return $custom.'\\'.$this->resolveFilterName($filter);
        }

        $config = config('eloquent-builder.namespace', 'App\\EloquentFilters\\');

        return $config.class_basename($model).'\\'.$this->resolveFilterName($filter);
    }

    private function sanitizeNamespace(string $namespace): array|string
    {
        return str_replace('\\\\', '\\', $namespace);
    }

    private function resolveFilterName(string $filter): string
    {
        return Str::studly($filter).'Filter';
    }
}
