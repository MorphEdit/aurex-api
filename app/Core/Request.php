<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $attributes  = [];
    private array $routeParams = [];
    private ?array $parsedJson = null;

    private function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array  $query,
        private readonly array  $body,
        private readonly array  $headers,
        private readonly string $ip,
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path   = parse_url($rawUri, PHP_URL_PATH) ?? '/';
        $uri    = '/' . trim($path, '/');
        $uri    = ($uri === '') ? '/' : $uri;

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name             = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name]   = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE']))  $headers['content-type']  = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['AUTHORIZATION'])) $headers['authorization'] = $_SERVER['AUTHORIZATION'];

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';

        return new self($method, $uri, $_GET, $_POST, $headers, $ip);
    }

    public function method(): string  { return $this->method; }
    public function path(): string    { return $this->uri; }
    public function ip(): string      { return $this->ip; }

    public function query(string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    public function all(): array
    {
        $contentType = $this->header('content-type', '');
        if (str_contains($contentType, 'application/json')) {
            if ($this->parsedJson === null) {
                $raw             = file_get_contents('php://input');
                $this->parsedJson = json_decode($raw, true) ?? [];
            }
            return array_merge($this->query, $this->parsedJson);
        }
        return array_merge($this->query, $this->body);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
