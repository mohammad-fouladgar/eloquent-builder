<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Console\PublishCommand;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Register facade.
     */
    public function register(): void
    {
        static $facadeRegistered = false;

        if (!$facadeRegistered) {
            $facadeRegistered = true;

            class_alias(Facade::class, 'EloquentBuilder');
        }

        parent::register();
    }

    /**
     * Register the helper command to publish the config file.
     */
    protected function registerConsole(): void
    {
        parent::registerConsole();

        $this->commands(PublishCommand::class);
    }

    protected function bootPublishes(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-builder');
    }
}
