<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $result = $this->authService->login($data['email'], $data['password']);

        $this->success($result, 'Login successful');
    }

    public function me(Request $request): void
    {
        $payload = $request->getAttribute('auth_user');
        $user    = $this->authService->getProfile((int) $payload['sub']);

        $this->success($user);
    }

    public function logout(Request $request): void
    {
        // JWT is stateless — client must discard the token.
        // Implement token blacklist (DB/Redis) here if needed.
        $this->success(null, 'Logged out successfully');
    }
}
