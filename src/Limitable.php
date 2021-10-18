<?php

namespace Itsmeabde\EasyOtp;

use Illuminate\Cache\RateLimiter;

trait Limitable
{
    /**
     * @param string $identifier
     * @return bool
     */
    protected function hasTooManyRegenerateAttempts(string $identifier): bool
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($identifier), 1
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param string $identifier
     * @return void
     */
    protected function incrementRegenerateAttempts(string $identifier): void
    {
        $this->limiter()->hit(
            $this->throttleKey($identifier), $this->getLimiterExtraTime()
        );
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param string $identifier
     * @return void
     */
    protected function clearRegenerateAttempts(string $identifier): void
    {
        $this->limiter()->clear($this->throttleKey($identifier));
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param string $identifier
     * @return string
     */
    protected function throttleKey(string $identifier): string
    {
        return "otpExtraTime:{$identifier}";
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function limiter()
    {
        return app(RateLimiter::class);
    }
}