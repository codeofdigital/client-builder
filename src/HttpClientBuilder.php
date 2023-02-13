<?php

namespace CodeOfDigital\ClientBuilder;

use BadMethodCallException;
use CodeOfDigital\ClientBuilder\Concerns\RateLimit;
use CodeOfDigital\ClientBuilder\Enums\HttpMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Macroable;
use OutOfBoundsException;
use RuntimeException;

abstract class HttpClientBuilder
{
    use RateLimit, Macroable {
        __call as macroCall;
    }

    protected PendingRequest $pendingRequest;

    protected array $pendingRequestCalls = [];

    protected string $baseUrl;

    protected string $token;

    private HttpMethod $method;

    private string $path;

    private array $query = [];

    private array $data = [];

    abstract protected function setBaseUrl(): void;

    public static function build(...$args): static
    {
        return app(static::class, $args);
    }

    public function __construct(private readonly HttpFactory $http)
    {
        $this->setBaseUrl();
        $this->validateBaseUrl();
    }

    protected function withRequest(PendingRequest $request): void
    {
        // TODO: do something with the initialized request
    }

    protected function withToken(string $token): static
    {
        return tap($this, fn () => $this->token = $token);
    }

    protected function to(HttpMethod $method, string $path): static
    {
        return tap($this, function () use ($method, $path) {
            $this->method = $method;
            $this->path = $path;
        });
    }

    protected function buildData(array $data): static
    {
        return tap($this, fn () => $this->data = array_merge_recursive($this->data, $data));
    }

    protected function buildQuery(array $query): static
    {
        return tap($this, fn () => $this->query = array_merge_recursive($this->query, $query));
    }

    public function payload(): array
    {
        return $this->data;
    }

    public function queryParams(): array
    {
        return $this->query;
    }

    public function send(): array
    {
        $this->initializeRequest();

        $url = $this->getFullPath();

        $key = $this->getRateLimiterKey(Str::slug($this->getPath()));

        if (self::isRateLimitEnabled()) $this->checkForMaxAttempts($key);

        $response = match ($this->method) {
            HttpMethod::GET, HttpMethod::HEAD => $this->pendingRequest->send($this->method->name, $url),
            HttpMethod::POST, HttpMethod::PUT, HttpMethod::PATCH, HttpMethod::DELETE => $this->pendingRequest->send($this->method->name, $url, ['json' => $this->payload()]),
            default => throw new OutOfBoundsException('HTTP method is invalid. Please provide a correct HTTP method.'),
        };

        if (self::isRateLimitEnabled()) $this->rateLimiterCounterIncrement($key);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'data' => isJson($response->body()) ? $response->json() : $response->body()
        ];
    }

    public function getBaseUrl(): string
    {
        if (isset($this->baseUrl))
            return $this->baseUrl;

        throw new RuntimeException('No baseUrl is configured.');
    }

    public function getPath(): ?string
    {
        return $this->path ?? null;
    }

    public function getRequest(): PendingRequest
    {
        return tap($this->pendingRequest, fn () => $this->initializeRequest());
    }

    public function getFullPath(): string
    {
        return (string) Str::of($this->path)->when(!empty($this->queryParams()), fn (Stringable $path): Stringable => $path->append('?', http_build_query($this->queryParams())));
    }

    private function validateBaseUrl(): void
    {
        if (!isset($this->baseUrl))
            throw new RuntimeException('No baseUrl is configured.');

        if (!Str::startsWith($this->baseUrl, ['http://', 'https://']))
            throw new RuntimeException('URL should starts with either http or https.');
    }

    private function initializeRequest(): void
    {
        if (!isset($this->pendingRequest)) {
            $this->pendingRequest = $this->http
                ->baseUrl($this->getBaseUrl())
                ->when($this->token, fn ($http) => $http->withToken($this->token));

            $this->withRequest($this->pendingRequest);

            foreach ($this->pendingRequestCalls as $call) {
                call_user_func_array([$this->pendingRequest, $call[0]], $call[1]);
            }
        }
    }

    public function __call(string $method, array $parameters)
    {
        if (isset($this->pendingRequest)) {
            if (method_exists($this->pendingRequest, $method)) {
                call_user_func_array([$this->pendingRequest, $method], $parameters);
                return $this;
            }
        } else {
            if (method_exists(PendingRequest::class, $method)) {
                $this->pendingRequestCalls[] = [$method, $parameters];
                return $this;
            }
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    public static function __callStatic(string $method, array $parameters)
    {
        if (method_exists(self::build(), $method))
            return (self::build())->{$method}(...$parameters);

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }
}