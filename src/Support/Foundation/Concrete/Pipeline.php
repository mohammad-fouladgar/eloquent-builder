<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Concrete;

use Closure;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Exceptions\FilterInstanceException;
use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
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

    public function __construct(protected ConfigRepository $config, Container $container = null)
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
     * @throws FilterException|Throwable
     */
    protected function carry(): Closure
    {
        return function ($stack, $name) {
            return function ($passable) use ($stack, $name) {
                try {
                    $pipeClass = $this->resolveFilter($name, $this->model);
                    $parameters = $this->pipes()[$name];

                    self::notFoundFilterHandler($pipeClass);

                    $pipe = $this->getContainer()->make($pipeClass);

                    self::filterInstanceHandler($pipe, $pipeClass);

                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}($passable, $stack, $parameters)
                        : $pipe($passable, $stack, $parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    private static function filterBasename(string $namespace): string
    {
        return class_basename($namespace);
    }

    /**
     * @throws Throwable
     */
    private static function notFoundFilterHandler($filterClass): void
    {
        throw_if(
            ! class_exists($filterClass),
            NotFoundFilterException::class,
            'Not found the filter: ' . self::filterBasename($filterClass)
        );
    }

    /**
     * @throws Throwable
     */
    private static function filterInstanceHandler($pipe, $filterClass): void
    {
        throw_if(
            ! $pipe instanceof Filter,
            FilterInstanceException::class,
            'The ' . self::filterBasename($filterClass) . ' filter must be an instance of Filter.'
        );
    }

}
