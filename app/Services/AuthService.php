<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Exceptions\HttpException;
use Firebase\JWT\JWT;

class AuthService
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new HttpException('Invalid email or password', 'INVALID_CREDENTIALS', 401);
        }

        if ($user['deleted_at'] !== null) {
            throw new HttpException('Account has been deactivated', 'ACCOUNT_DEACTIVATED', 403);
        }

        return [
            'token'      => $this->generateToken($user),
            'expires_in' => (int) ($_ENV['JWT_EXPIRY'] ?? 3600),
            'user'       => $this->sanitizeUser($user),
        ];
    }

    public function getProfile(int $userId): array
    {
        $user = $this->userRepo->findById($userId);

        if (!$user) {
            throw new HttpException('User not found', 'USER_NOT_FOUND', 404);
        }

        return $this->sanitizeUser($user);
    }

    private function generateToken(array $user): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? '';
        $expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
        $now    = time();

        return JWT::encode([
            'iss'  => $_ENV['APP_NAME'] ?? 'AurexAPI',
            'sub'  => $user['id'],
            'role' => $user['role'],
            'iat'  => $now,
            'exp'  => $now + $expiry,
        ], $secret, 'HS256');
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}
