<?php

declare(strict_types=1);

namespace App\Repositories;

class EmployeeRepository extends BaseRepository
{
    protected string $table = 'employees';

    public function findByEmpCode(string $empCode): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE emp_code = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$empCode]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE email = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function paginate(int $page, int $perPage, array $filters): array
    {
        $offset  = ($page - 1) * $perPage;
        $params  = [];
        $clauses = ['e.deleted_at IS NULL'];

        if (!empty($filters['search'])) {
            $clauses[] = '(e.full_name LIKE ? OR e.emp_code LIKE ? OR e.email LIKE ?)';
            $params[]  = "%{$filters['search']}%";
            $params[]  = "%{$filters['search']}%";
            $params[]  = "%{$filters['search']}%";
        }

        if (!empty($filters['dept_id'])) {
            $clauses[] = 'e.dept_id = ?';
            $params[]  = $filters['dept_id'];
        }

        if (!empty($filters['position_id'])) {
            $clauses[] = 'e.position_id = ?';
            $params[]  = $filters['position_id'];
        }

        if (!empty($filters['status'])) {
            $clauses[] = 'e.status = ?';
            $params[]  = $filters['status'];
        }

        $where = implode(' AND ', $clauses);

        $countSql = "SELECT COUNT(*) FROM {$this->table} e WHERE {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $dataSql = "SELECT
                e.id, e.emp_code, e.full_name, e.email, e.phone,
                e.dept_id, d.name AS dept_name,
                e.position_id, p.name AS position_name,
                e.hire_date, e.salary, e.status,
                e.created_at, e.updated_at
            FROM {$this->table} e
            LEFT JOIN departments d ON d.id = e.dept_id AND d.deleted_at IS NULL
            LEFT JOIN positions   p ON p.id = e.position_id AND p.deleted_at IS NULL
            WHERE {$where}
            ORDER BY e.created_at DESC
            LIMIT ? OFFSET ?";

        $dataStmt = $this->db->prepare($dataSql);
        $dataStmt->execute([...$params, $perPage, $offset]);

        return ['data' => $dataStmt->fetchAll(), 'total' => $total];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                e.id, e.emp_code, e.full_name, e.email, e.phone,
                e.dept_id, d.name AS dept_name,
                e.position_id, p.name AS position_name,
                e.hire_date, e.salary, e.status,
                e.created_at, e.updated_at
             FROM {$this->table} e
             LEFT JOIN departments d ON d.id = e.dept_id AND d.deleted_at IS NULL
             LEFT JOIN positions   p ON p.id = e.position_id AND p.deleted_at IS NULL
             WHERE e.id = ? AND e.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
                (emp_code, full_name, email, phone, dept_id, position_id,
                 hire_date, salary, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $data['emp_code'],
            $data['full_name'],
            $data['email']       ?: null,
            $data['phone']       ?: null,
            $data['dept_id']     ?: null,
            $data['position_id'] ?: null,
            $data['hire_date'],
            $data['salary'],
            $data['status'],
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET emp_code = ?, full_name = ?, email = ?, phone = ?,
                 dept_id = ?, position_id = ?,
                 hire_date = ?, salary = ?, status = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([
            $data['emp_code'],
            $data['full_name'],
            $data['email']       ?: null,
            $data['phone']       ?: null,
            $data['dept_id']     ?: null,
            $data['position_id'] ?: null,
            $data['hire_date'],
            $data['salary'],
            $data['status'],
            $id,
        ]);
    }
}
