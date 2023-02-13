<?php

namespace CodeOfDigital\ClientBuilder\Concerns;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

trait RateLimit
{
    protected string $prefix = '';

    protected string|int $identifier;

    protected int $threshold;

    protected bool $enableRateLimit = true;

    public function isRateLimitEnabled(): bool
    {
        $enableRateLimit = Config::get('builder.rate_limit.enabled', true);

        if (!$enableRateLimit) return false;

        return $this->enableRateLimit;
    }

    public function enableRateLimit(): static
    {
        return $this->manageRateLimitConfig(true);
    }

    public function disableRateLimit(): static
    {
        return $this->manageRateLimitConfig(false);
    }

    public function setIdentifier(string|int $identifier): static
    {
        return tap($this, fn () => $this->identifier = $identifier);
    }

    public function getRateLimiterKey(string $suffix): string
    {
        $declared_class = explode(DIRECTORY_SEPARATOR, static::class);
        $this->prefix = empty($this->prefix) ? Str::kebab($declared_class[array_key_last($declared_class)]) : $this->prefix;
        $identifier = $this->identifier ?? Str::replace('.', '', request()->ip());
        return sprintf('rate-limit@%s-%s-%s', $this->prefix, Str::kebab($suffix), $identifier);
    }

    public function checkForMaxAttempts(string $key): void
    {
        if (RateLimiter::tooManyAttempts($key, $this->threshold)) {
            $seconds = RateLimiter::availableIn($key);
            throw new ThrottleRequestsException("Too many attempts. You may try again in {$seconds} seconds.");
        }
    }

    public function rateLimiterCounterIncrement(string $key): void
    {
        RateLimiter::hit($key);

        $threshold = $this->threshold ?? Config::get('builder.rate_limit.threshold', 0);

        if (RateLimiter::attempts($key) > $threshold) {
            $seconds = RateLimiter::availableIn($key);
            throw new ThrottleRequestsException("Too many attempts. You may try again in {$seconds} seconds.");
        }
    }

    private function manageRateLimitConfig(bool $value): static
    {
        return tap($this, fn () => $this->enableRateLimit = $value);
    }
}