<?php

declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

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

    public function paginate(int $page, int $perPage, ?string $search): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where  = 'deleted_at IS NULL';

        if ($search) {
            $where   .= ' AND (name LIKE ? OR email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Data
        $dataStmt = $this->db->prepare(
            "SELECT id, name, email, role, created_at, updated_at
             FROM {$this->table}
             WHERE {$where}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $dataStmt->execute([...$params, $perPage, $offset]);

        return ['data' => $dataStmt->fetchAll(), 'total' => $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (name, email, password, role, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([$data['name'], $data['email'], $data['password'], $data['role']]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET name = ?, email = ?, role = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$data['name'], $data['email'], $data['role'], $id]);
    }
}
