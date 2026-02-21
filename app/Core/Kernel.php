<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use Throwable;
use App\Middleware\CorsMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;

class Kernel
{
    private Router  $router;
    private Request $request;

    public function __construct()
    {
        $this->loadEnvironment();
        $this->request = Request::capture();
        $this->router  = new Router();
        $this->loadRoutes();
    }

    public function handle(): void
    {
        try {
            $globalMiddleware = [
                new CorsMiddleware(),
                new LoggingMiddleware(),
                new RateLimitMiddleware(),
            ];

            $chain = new MiddlewareChain(
                $globalMiddleware,
                fn(Request $req) => $this->router->dispatch($req)
            );

            $chain->run($this->request);

        } catch (ValidationException $e) {
            Response::json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'errors'  => $e->getErrors(),
                ],
            ], 422);

        } catch (HttpException $e) {
            Response::json([
                'success' => false,
                'error'   => [
                    'code'    => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
            ], $e->getCode() ?: 400);

        } catch (Throwable $e) {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

            Logger::error('unhandled_exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            Response::json([
                'success' => false,
                'error'   => [
                    'code'    => 'INTERNAL_SERVER_ERROR',
                    'message' => $debug ? $e->getMessage() : 'An unexpected error occurred',
                    'trace'   => $debug ? explode("\n", $e->getTraceAsString()) : null,
                ],
            ], 500);
        }
    }

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->safeLoad();
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        require BASE_PATH . '/routes/api.php';
    }
}
