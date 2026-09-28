<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Closure;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Fouladgar\EloquentBuilder\Support\Foundation\FilterResolverTrait;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use Throwable;

class Pipeline extends BasePipeline
{
    use FilterResolverTrait;

    protected string $customNamespace = '';

    private Model $model;

    public function __construct(protected ConfigRepository $config, ?Container $container = null)
    {
        parent::__construct($container);
    }

    public function model($model): static
    {
        $this->model = $model;

        return $this;
    }

    public function customNamespace(string $namespace = ''): static
    {
        $this->customNamespace = $namespace;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_keys($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    /**
     * @throws Throwable
     */
    protected function carry(): Closure
    {
        return fn ($stack, $name) => function ($passable) use ($stack, $name) {
            try {
                $pipeClass = $this->resolveFilter($name, $this->model);
                $parameters = $this->pipes()[$name];

                $this->notFoundFilterHandler($pipeClass);

                $pipe = $this->getContainer()->make($pipeClass);

                $this->filterInstanceHandler($pipe, $pipeClass);

                $pipe->authorizeResolved();

                $carry = method_exists($pipe, $this->method)
                    ? $pipe->{$this->method}($passable, $stack, $parameters)
                    : $pipe($passable, $stack, $parameters);

                return $this->handleCarry($carry);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    private function filterBasename(string $namespace): string
    {
        return class_basename($namespace);
    }

    /**
     * @throws Throwable
     */
    private function notFoundFilterHandler($filterClass): void
    {
        throw_if(
            ! class_exists($filterClass),
            FilterException::filterNotFound($this->filterBasename($filterClass)),
        );
    }

    /**
     * @throws Throwable
     */
    private function filterInstanceHandler($pipe, $filterClass): void
    {
        throw_if(
            ! $pipe instanceof Filter,
            FilterException::filterInstance($this->filterBasename($filterClass))
        );
    }
}
