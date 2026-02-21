<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Exceptions\HttpException;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * @param string[] $allowedRoles  e.g. ['super_admin'] or ['admin', 'super_admin']
     */
    public function __construct(private readonly array $allowedRoles) {}

    public function handle(Request $request, callable $next): void
    {
        $user = $request->getAttribute('auth_user');

        if (!$user) {
            throw new HttpException('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $role = $user['role'] ?? '';

        if (!in_array($role, $this->allowedRoles, true)) {
            throw new HttpException(
                'You do not have permission to perform this action',
                'FORBIDDEN',
                403
            );
        }

        $next($request);
    }
}
