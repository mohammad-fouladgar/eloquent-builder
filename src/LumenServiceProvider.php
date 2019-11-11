<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Console\PublishCommand;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Register facade.
     */
    public function register()
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
    protected function registerConsole()
    {
        parent::registerConsole();

        $this->commands(PublishCommand::class);
    }

    protected function bootPublishes()
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-builder');
    }
}
