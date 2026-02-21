<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Exceptions\HttpException;
use App\Core\Logger;

class UserService
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function list(int $page, int $perPage, ?string $search): array
    {
        return $this->userRepo->paginate($page, $perPage, $search);
    }

    public function findById(int $id): array
    {
        $user = $this->userRepo->findById($id);

        if (!$user) {
            throw new HttpException('User not found', 'USER_NOT_FOUND', 404);
        }

        unset($user['password']);
        return $user;
    }

    public function create(array $data): array
    {
        if ($this->userRepo->findByEmail($data['email'])) {
            throw new HttpException('Email is already taken', 'EMAIL_TAKEN', 409);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $id = $this->userRepo->create($data);

        Logger::info('user.created', ['user_id' => $id, 'email' => $data['email']]);

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->findById($id); // throws 404 if not found

        $existing = $this->userRepo->findByEmail($data['email']);
        if ($existing && (int) $existing['id'] !== $id) {
            throw new HttpException('Email is already taken', 'EMAIL_TAKEN', 409);
        }

        $this->userRepo->update($id, $data);

        Logger::info('user.updated', ['user_id' => $id]);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id); // throws 404 if not found

        $this->userRepo->softDelete($id);

        Logger::info('user.deleted', ['user_id' => $id]);
    }
}
