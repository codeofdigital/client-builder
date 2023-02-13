<?php

namespace CodeOfDigital\ClientBuilder\Contracts;

interface CacheableInterface
{
    public function getCachePayload(array $request): array;
}