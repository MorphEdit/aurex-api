<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DepartmentRepository;
use App\Exceptions\HttpException;
use App\Core\Logger;

class DepartmentService
{
    private DepartmentRepository $repo;

    public function __construct()
    {
        $this->repo = new DepartmentRepository();
    }

    public function list(int $page, int $perPage, ?string $search): array
    {
        return $this->repo->paginate($page, $perPage, $search);
    }

    public function findById(int $id): array
    {
        $dept = $this->repo->findById($id);

        if (!$dept) {
            throw new HttpException('Department not found', 'DEPARTMENT_NOT_FOUND', 404);
        }

        return $dept;
    }

    public function create(array $data): array
    {
        if ($this->repo->findByName($data['name'])) {
            throw new HttpException('Department name already exists', 'NAME_TAKEN', 409);
        }

        $id = $this->repo->create($data);

        Logger::info('department.created', ['dept_id' => $id, 'name' => $data['name']]);

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->findById($id);

        $existing = $this->repo->findByName($data['name']);
        if ($existing && (int) $existing['id'] !== $id) {
            throw new HttpException('Department name already exists', 'NAME_TAKEN', 409);
        }

        $this->repo->update($id, $data);

        Logger::info('department.updated', ['dept_id' => $id]);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);

        $this->repo->softDelete($id);

        Logger::info('department.deleted', ['dept_id' => $id]);
    }
}
