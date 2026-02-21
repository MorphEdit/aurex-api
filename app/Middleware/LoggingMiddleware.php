<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Core\Logger;

class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        $startTime = microtime(true);

        Logger::info('request.in', [
            'method' => $request->method(),
            'path'   => $request->path(),
            'ip'     => $request->ip(),
        ]);

        $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Logger::info('request.out', [
            'method'      => $request->method(),
            'path'        => $request->path(),
            'duration_ms' => $duration,
        ]);
    }
}
