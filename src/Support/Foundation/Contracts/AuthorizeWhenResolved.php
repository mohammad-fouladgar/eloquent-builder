<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

interface AuthorizeWhenResolved
{
    /**
     * Authorize the given filter instance.
     */
    public function authorizeResolved();
}
