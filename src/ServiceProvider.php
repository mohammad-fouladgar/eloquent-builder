<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Console\FilterMakeCommand;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\AuthorizeWhenResolved;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->bootPublishes();

        $this->registerMacros();

        $this->app->afterResolving(AuthorizeWhenResolved::class, static function ($resolved) {
            $resolved->authorizeResolved();
        });
    }

    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->registerBindings();

        $this->registerConsole();
    }

    protected function bootPublishes(): void
    {
        $configPath = $this->configPath();

        $this->mergeConfigFrom($configPath, 'eloquent-builder');

        $this->publishes([$configPath => config_path('eloquent-builder.php')], 'config');
    }

    /**
     * @return string
     */
    protected function configPath(): string
    {
        return __DIR__.'/../config/eloquent-builder.php';
    }

    /**
     * Register console commands.
     */
    protected function registerConsole(): void
    {
        $this->commands(FilterMakeCommand::class);
    }

    private function registerBindings()
    {
        $this->app->bind(Factory::class, FilterFactory::class);

        $this->app->singleton('eloquentbuilder', static function () {
            return new EloquentBuilder(new FilterFactory());
        });
    }

    private function registerMacros()
    {
        Collection::macro('getFilters', function () {
            $filters = $this->filter(static function ($value, $filter) {
                if (is_array($value)) {
                    $result = [];
                    array_walk_recursive($value, static function ($val) use (&$result) {
                        if (isset($val) && strlen($val) !== 0) {
                            $result[] = $val;
                        }
                    });

                    return !empty($result);
                }

                return !is_int($filter) && (isset($value) && strlen($value) !== 0);
            });

            return $filters->all();
        });
    }
}
