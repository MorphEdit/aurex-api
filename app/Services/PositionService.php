<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PositionRepository;
use App\Exceptions\HttpException;
use App\Core\Logger;

class PositionService
{
    private PositionRepository $repo;

    public function __construct()
    {
        $this->repo = new PositionRepository();
    }

    public function list(int $page, int $perPage, ?string $search): array
    {
        return $this->repo->paginate($page, $perPage, $search);
    }

    public function findById(int $id): array
    {
        $position = $this->repo->findById($id);

        if (!$position) {
            throw new HttpException('Position not found', 'POSITION_NOT_FOUND', 404);
        }

        return $position;
    }

    public function create(array $data): array
    {
        $id = $this->repo->create($data);

        Logger::info('position.created', ['position_id' => $id, 'name' => $data['name']]);

        return $this->findById($id);
    }

    public function update(int $id, array $data): array
    {
        $this->findById($id);

        $this->repo->update($id, $data);

        Logger::info('position.updated', ['position_id' => $id]);

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);

        $this->repo->softDelete($id);

        Logger::info('position.deleted', ['position_id' => $id]);
    }
}
