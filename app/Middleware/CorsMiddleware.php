<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        $allowedOrigins = array_map(
            'trim',
            explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*')
        );

        $origin = $request->header('origin', '');

        if (in_array('*', $allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        } elseif ($origin && in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next($request);
    }
}
