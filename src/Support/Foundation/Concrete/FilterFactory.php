<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as Factory;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterResolverTrait;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FilterFactory implements Factory
{
    use FilterResolverTrait;

    protected string $namespace = '';

    protected string $customNamespace = '';

    /**
     * {@inheritdoc}
     */
    public function make(string $filter, Model $model): Filter
    {
        $this->namespace = $this->resolveFilter($filter, $model);

        if (! $this->filterExists()) {
            $this->notFoundFilter();
        }

        $filter = app($this->namespace);

        if (! $filter instanceof Filter) {
            $this->invalidFilter();
        }

        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomNamespace(string $namespace = ''): self
    {
        $this->customNamespace = $namespace;

        return $this;
    }

    protected function filterExists(): bool
    {
        return class_exists($this->namespace);
    }

    /**
     * @throws NotFoundFilterException
     */
    protected function notFoundFilter(): void
    {
        throw new NotFoundFilterException('Not found the filter: '.$this->filterBasename());
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function invalidFilter(): void
    {
        throw new InvalidArgumentException('The '.$this->filterBasename().' filter must be an instance of Filter.');
    }

    private function filterBasename(): string
    {
        return class_basename($this->namespace);
    }
}
