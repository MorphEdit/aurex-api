<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Exceptions\HttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new HttpException('No token provided', 'UNAUTHENTICATED', 401);
        }

        try {
            $secret  = $_ENV['JWT_SECRET'] ?? '';
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Store payload as array for easy access
            $request->setAttribute('auth_user', (array) $decoded);

        } catch (ExpiredException) {
            throw new HttpException('Token has expired', 'TOKEN_EXPIRED', 401);
        } catch (SignatureInvalidException) {
            throw new HttpException('Invalid token signature', 'TOKEN_INVALID', 401);
        } catch (BeforeValidException) {
            throw new HttpException('Token not yet valid', 'TOKEN_INVALID', 401);
        } catch (\Exception) {
            throw new HttpException('Invalid token', 'TOKEN_INVALID', 401);
        }

        $next($request);
    }
}
