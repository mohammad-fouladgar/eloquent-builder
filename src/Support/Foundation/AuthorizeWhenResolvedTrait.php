<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use Illuminate\Auth\Access\AuthorizationException;

trait AuthorizeWhenResolvedTrait
{
    /**
     * authorize the filter instance.
     */
    public function authorizeResolved()
    {
        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }
    }

    /**
     * Determine if the filter passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('This filter action is unauthorized.');
    }
}
