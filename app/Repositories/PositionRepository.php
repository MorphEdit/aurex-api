<?php

declare(strict_types=1);

namespace App\Repositories;

class PositionRepository extends BaseRepository
{
    protected string $table = 'positions';

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
             ORDER BY level ASC, name ASC
             LIMIT ? OFFSET ?"
        );
        $dataStmt->execute([...$params, $perPage, $offset]);

        return ['data' => $dataStmt->fetchAll(), 'total' => $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (name, level, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())"
        );
        $stmt->execute([$data['name'], (int) $data['level']]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET name = ?, level = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$data['name'], (int) $data['level'], $id]);
    }
}
