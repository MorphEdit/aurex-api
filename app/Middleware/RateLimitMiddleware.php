<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Exceptions\HttpException;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int    $limit;
    private int    $window;
    private string $storePath;

    public function __construct()
    {
        $this->limit     = (int) ($_ENV['RATE_LIMIT']        ?? 60);
        $this->window    = (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60);
        $this->storePath = BASE_PATH . '/storage/rate_limits';

        if (!is_dir($this->storePath)) {
            mkdir($this->storePath, 0755, true);
        }
    }

    public function handle(Request $request, callable $next): void
    {
        $key  = md5($request->ip());
        $file = "{$this->storePath}/{$key}.json";
        $now  = time();

        $data = ['count' => 0, 'reset_at' => $now + $this->window];

        if (file_exists($file)) {
            $stored = json_decode(file_get_contents($file), true);
            if ($stored && $stored['reset_at'] > $now) {
                $data = $stored;
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        header("X-RateLimit-Limit: {$this->limit}");
        header('X-RateLimit-Remaining: ' . max(0, $this->limit - $data['count']));
        header("X-RateLimit-Reset: {$data['reset_at']}");

        if ($data['count'] > $this->limit) {
            throw new HttpException('Too Many Requests', 'RATE_LIMIT_EXCEEDED', 429);
        }

        $next($request);
    }
}
