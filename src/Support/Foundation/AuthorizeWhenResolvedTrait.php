<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation;

use Illuminate\Auth\Access\AuthorizationException;

trait AuthorizeWhenResolvedTrait
{
    /**
     * authorize the filter instance.
     *
     * @throws AuthorizationException
     */
    public function authorizeResolved(): void
    {
        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }
    }

    /**
     * Determine if the filter passes the authorization check.
     */
    protected function passesAuthorization(): bool
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('This filter action is unauthorized.');
    }
}
