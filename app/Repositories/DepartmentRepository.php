<?php

declare(strict_types=1);

namespace App\Repositories;

class DepartmentRepository extends BaseRepository
{
    protected string $table = 'departments';

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE name = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$name]);
        return $stmt->fetch() ?: null;
    }

    public function paginate(int $page, int $perPage, ?string $search): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where  = 'deleted_at IS NULL';

        if ($search) {
            $where   .= ' AND name LIKE ?';
            $params[] = "%{$search}%";
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $dataStmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE {$where}
             ORDER BY name ASC
             LIMIT ? OFFSET ?"
        );
        $dataStmt->execute([...$params, $perPage, $offset]);

        return ['data' => $dataStmt->fetchAll(), 'total' => $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (name, description, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())"
        );
        $stmt->execute([$data['name'], $data['description'] ?? null]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET name = ?, description = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
    }
}
