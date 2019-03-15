<?php

namespace Fouladgar\EloquentBuilder\Tests;

trait LumenServiceRegister
{
    protected function getPackageProviders($app)
    {
        return ['Fouladgar\\EloquentBuilder\\LumenServiceProvider'];
    }
}
