<?php

namespace CodeOfDigital\ClientBuilder\Concerns;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait Cacheable
{
    protected CacheRepository $cacheRepository;

    protected int $cacheMinutes;

    protected bool $enableCaching = true;

    public function isCacheEnabled(): bool
    {
        $enabledCaching = Config::get('builder.cache.enabled', true);

        if (!$enabledCaching) return false;

        return $this->enableCaching;
    }

    public function enableCache(): static
    {
        return $this->manageCacheConfig(true);
    }

    public function disableCache(): static
    {
        return $this->manageCacheConfig(false);
    }

    public function getCacheRepository(): CacheRepository
    {
        if (isset($this->cacheRepository))
            return $this->cacheRepository;

        return app('cache.store');
    }

    public function getCachePayload(array $request = []): array
    {
        $payload = [...$request];

        if (method_exists(static::class, 'payload'))
            $payload = [...$payload, ...$this->payload()];

        if (method_exists(static::class, 'queryParams'))
            $payload = [...$payload, ...$this->queryParams()];

        return $payload;
    }

    public function getCacheKey(string|int $identifier, mixed $args = null): string
    {
        $args = serialize($args ?? $this->getCachePayload());
        $declared_class = explode(DIRECTORY_SEPARATOR, static::class);
        return sprintf('client@%s-%s-%s', Str::kebab($declared_class[array_key_last($declared_class)]), $identifier, md5($args));
    }

    public function getCacheTTL(): float|int
    {
        if (isset($this->cacheMinutes))
            return $this->cacheMinutes * 60;

        return Config::get('baseapp.cache.cache_ttl', 30) * 60;
    }

    private function manageCacheConfig(bool $value): static
    {
        return tap($this, fn () => $this->enableCaching = $value);
    }
}