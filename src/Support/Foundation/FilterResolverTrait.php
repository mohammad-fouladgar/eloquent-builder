<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait FilterResolverTrait
{
    /**
     * Resolve namespace filters.
     *
     * @param string $filter
     * @param Model  $model
     */
    private function resolveFilter(string $filter, Model $model): void
    {
        $namespace = $this->sanitizeNamespace($this->resolveNamespace($filter, $model));

        $this->setNamespace($namespace);
    }

    /**
     * Resolve default or custom namespace.
     *
     * @param string $filter
     * @param Model  $model
     *
     * @return string
     */
    private function resolveNamespace(string $filter, Model $model): string
    {
        if ($custom = $this->getCustomNamespace()) {
            return $custom.'\\'.$this->resolveFilterName($filter);
        }

        $config = config('eloquent-builder.namespace', 'App\\EloquentFilters\\');

        return $config.class_basename($model).'\\'.$this->resolveFilterName($filter);
    }

    /**
     * Sanitizing a namespace.
     *
     * @param $namespace
     *
     * @return string|string[]
     */
    private function sanitizeNamespace($namespace)
    {
        return str_replace('\\\\', '\\', $namespace);
    }

    /**
     * @param string $filter
     *
     * @return string
     */
    private function resolveFilterName(string $filter): string
    {
        return Str::studly($filter).'Filter';
    }
}
