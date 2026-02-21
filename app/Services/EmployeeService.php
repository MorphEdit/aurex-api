<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EmployeeRepository;
use App\Exceptions\HttpException;
use App\Core\Logger;

class EmployeeService
{
    private EmployeeRepository $repo;

    public function __construct()
    {
        $this->repo = new EmployeeRepository();
    }

    public function list(int $page, int $perPage, array $filters): array
    {
        return $this->repo->paginate($page, $perPage, $filters);
    }

    public function findById(int $id): array
    {
        $employee = $this->repo->findById($id);

        if (!$employee) {
            throw new HttpException('Employee not found', 'EMPLOYEE_NOT_FOUND', 404);
        }

        return $employee;
    }

    public function create(array $data): array
    {
        if ($this->repo->findByEmpCode($data['emp_code'])) {
            throw new HttpException('Employee code already exists', 'EMP_CODE_TAKEN', 409);
        }

        if (!empty($data['email']) && $this->repo->findByEmail($data['email'])) {
            throw new HttpException('Email already exists', 'EMAIL_TAKEN', 409);
        }

        $id = $this->repo->create($data);

        Logger::info('employee.created', ['employee_id' => $id, 'emp_code' => $data['emp_code']]);

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->findById($id);

        $existing = $this->repo->findByEmpCode($data['emp_code']);
        if ($existing && (int) $existing['id'] !== $id) {
            throw new HttpException('Employee code already exists', 'EMP_CODE_TAKEN', 409);
        }

        if (!empty($data['email'])) {
            $existingEmail = $this->repo->findByEmail($data['email']);
            if ($existingEmail && (int) $existingEmail['id'] !== $id) {
                throw new HttpException('Email already exists', 'EMAIL_TAKEN', 409);
            }
        }

        $this->repo->update($id, $data);

        Logger::info('employee.updated', ['employee_id' => $id]);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);

        $this->repo->softDelete($id);

        Logger::info('employee.deleted', ['employee_id' => $id]);
    }
}
