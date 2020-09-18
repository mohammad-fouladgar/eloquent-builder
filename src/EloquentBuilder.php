<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class EloquentBuilder
{
    /**
     * The filter factory.
     *
     * @var
     */
    protected $filterFactory;

    /**
     * Custom filters namespace.
     *
     * @var string
     */
    protected $filterNamespace = '';

    protected $failFilterException = false;

    /**
     * EloquentBuilder constructor.
     *
     * @param FilterFactory $filterFactory
     */
    public function __construct(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
        $this->failFilterException = config('eloquent-builder.fail_filter_exception');
    }

    /**
     * Create a new EloquentBuilder for a request and model.
     *
     * @param string|EloquentModel|Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws Exceptions\NotFoundFilterException
     */
    public function to($query, array $filters = null): Builder
    {
        /** @var Builder $query */
        $query = $this->resolveQuery($query);

        if (!$filters) {
            return $query;
        }

        $this->applyFilters($query, $this->getFilters($filters));

        return $query;
    }

    /**
     * Set custom filters namespace.
     *
     * @param string $namespace
     *
     * @return EloquentBuilder
     */
    public function setFilterNamespace(string $namespace = ''): self
    {
        $this->filterNamespace = $namespace;

        return $this;
    }


    public function failOrSkipFilter(bool $fail = false): self
    {
        $this->failFilterException = $fail;

        return $this;
    }

    /**
     * Resolve the incoming query to Builder.
     *
     * @param string|EloquentModel|Builder $query
     *
     * @return Builder
     */
    private function resolveQuery($query): Builder
    {
        if (is_string($query)) {
            return $query::query();
        }

        if ($query instanceof EloquentModel) {
            return $query->query();
        }

        return $query;
    }

    /**
     * Returns only filters that have value.
     *
     * @param array $filters
     *
     * @return array
     */
    private function getFilters(array $filters = []): array
    {
        return collect($filters)->getFilters();
    }

    /**
     * Apply filters to Query Builder.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     *
     * @throws NotFoundFilterException
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        try {
            foreach ($filters as $filter => $value) {
                $query = $this->filterFactory->setCustomNamespace($this->filterNamespace)
                    ->make($filter, $query->getModel())
                    ->apply($query, $value);
            }
        } catch (NotFoundFilterException $ex) {
            throw_if($this->failFilterException, $ex);
        }

        return $query;
    }
}
