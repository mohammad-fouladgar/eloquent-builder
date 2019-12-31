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

    /**
     * Namespace of the model filter.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Custom filters namespace.
     *
     * @var string
     */
    protected $customNamespace = '';

    /**
     * {@inheritdoc}
     */
    public function make(string $filter, Model $model): Filter
    {
        $this->resolveFilter($filter, $model);

        if (!$this->filterExists()) {
            $this->notFoundFilter();
        }

        $filter = app($this->getNamespace());

        if (!$filter instanceof Filter) {
            $this->invalidFilter();
        }

        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomNamespace(string $namespace = ''): self
    {
        $this->customNamespace = $namespace;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomNamespace(): string
    {
        return $this->customNamespace;
    }

    /**
     * Check if a filter exists.
     *
     * @return bool
     */
    protected function filterExists(): bool
    {
        return class_exists($this->namespace);
    }

    /**
     * Handle a not found filter.
     *
     * @throws NotFoundFilterException
     */
    protected function notFoundFilter(): void
    {
        throw new NotFoundFilterException('Not found the filter: '.$this->filterBasename());
    }

    /**
     * Handle a failed implementation filter.
     *
     * @throws InvalidArgumentException
     */
    protected function invalidFilter(): void
    {
        throw new InvalidArgumentException('The '.$this->filterBasename().' filter must be an instance of Filter.');
    }

    /**
     * @return string
     */
    private function filterBasename(): string
    {
        return class_basename($this->namespace);
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        return $this->namespace;
    }
}
