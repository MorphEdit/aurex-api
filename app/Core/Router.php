<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;

class Router
{
    private array  $routes        = [];
    private string $groupPrefix   = '';
    private array  $groupMiddleware = [];

    // ── Registration ────────────────────────────────────────────────────────────

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $prevPrefix     = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->groupPrefix     = $prevPrefix . $prefix;
        $this->groupMiddleware = array_merge($prevMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix     = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────────

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            array_shift($matches);
            $params = !empty($route['params'])
                ? array_combine($route['params'], $matches)
                : [];

            $request->setRouteParams($params);

            $chain = new MiddlewareChain(
                $route['middleware'],
                fn(Request $req) => $this->callHandler($route['handler'], $req)
            );

            $chain->run($request);
            return;
        }

        // Check if any route matches the URI (wrong method)
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $uri)) {
                throw new HttpException('Method Not Allowed', 'METHOD_NOT_ALLOWED', 405);
            }
        }

        throw new HttpException('Route Not Found', 'NOT_FOUND', 404);
    }

    // ── Private helpers ───────────────────────────────────────────────────────────

    private function addRoute(string $method, string $path, string $handler, array $middleware): void
    {
        $fullPath      = $this->groupPrefix . $path;
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->buildPattern($fullPath),
            'handler'    => $handler,
            'middleware' => $allMiddleware,
            'params'     => $this->extractParamNames($fullPath),
        ];
    }

    private function buildPattern(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        $pattern    = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $normalized);
        return '@^' . $pattern . '$@';
    }

    private function extractParamNames(string $path): array
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $path, $matches);
        return $matches[1];
    }

    private function callHandler(string $handler, Request $request): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $fullClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($fullClass)) {
            throw new HttpException("Controller [{$controllerName}] not found", 'INTERNAL_ERROR', 500);
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $method)) {
            throw new HttpException("Method [{$method}] not found on [{$controllerName}]", 'INTERNAL_ERROR', 500);
        }

        $controller->$method($request);
    }
}
