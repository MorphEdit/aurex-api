<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function success(mixed $data = null, string $message = 'OK', int $status = 200, array $meta = []): void
    {
        $payload = ['success' => true, 'data' => $data];
        if ($message !== 'OK') $payload['message'] = $message;
        if (!empty($meta))    $payload['meta']    = $meta;
        self::json($payload, $status);
    }

    public static function created(mixed $data = null, string $message = 'Created'): void
    {
        self::success($data, $message, 201);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }

    public static function paginated(array $data, int $total, int $page, int $perPage): void
    {
        self::success($data, 'OK', 200, [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / max($perPage, 1)),
        ]);
    }
}
