<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO    $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE {$this->primaryKey} = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET deleted_at = NOW(), updated_at = NOW()
             WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    protected function buildWhereClause(array $conditions): array
    {
        $clauses = ['deleted_at IS NULL'];
        $params  = [];

        foreach ($conditions as $clause => $value) {
            $clauses[] = $clause;
            if (is_array($value)) {
                $params = array_merge($params, $value);
            } else {
                $params[] = $value;
            }
        }

        return ['where' => implode(' AND ', $clauses), 'params' => $params];
    }
}
