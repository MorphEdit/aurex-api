<?php

declare(strict_types=1);

namespace App\DTO;

class EmployeeDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $empCode,
        public readonly string  $fullName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?int    $deptId,
        public readonly ?string $deptName,
        public readonly ?int    $positionId,
        public readonly ?string $positionName,
        public readonly string  $hireDate,
        public readonly float   $salary,
        public readonly string  $status,
        public readonly string  $createdAt,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:           (int)   $data['id'],
            empCode:               $data['emp_code'],
            fullName:              $data['full_name'],
            email:                 $data['email']        ?? null,
            phone:                 $data['phone']        ?? null,
            deptId:       $data['dept_id']     ? (int)   $data['dept_id']     : null,
            deptName:              $data['dept_name']    ?? null,
            positionId:   $data['position_id'] ? (int)   $data['position_id'] : null,
            positionName:          $data['position_name'] ?? null,
            hireDate:              $data['hire_date'],
            salary:       (float)  $data['salary'],
            status:                $data['status'],
            createdAt:             $data['created_at'],
            updatedAt:             $data['updated_at']   ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'emp_code'      => $this->empCode,
            'full_name'     => $this->fullName,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'dept_id'       => $this->deptId,
            'dept_name'     => $this->deptName,
            'position_id'   => $this->positionId,
            'position_name' => $this->positionName,
            'hire_date'     => $this->hireDate,
            'salary'        => $this->salary,
            'status'        => $this->status,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}
