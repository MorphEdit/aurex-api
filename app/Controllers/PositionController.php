<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\PositionService;

class PositionController extends BaseController
{
    private PositionService $posService;

    public function __construct()
    {
        $this->posService = new PositionService();
    }

    public function index(Request $request): void
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        $search  = $request->query('search');

        ['data' => $items, 'total' => $total] = $this->posService->list($page, $perPage, $search);

        $this->paginated($items, $total, $page, $perPage);
    }

    public function show(Request $request): void
    {
        $position = $this->posService->findById((int) $request->param('id'));
        $this->success($position);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'  => 'required|min:2|max:100',
            'level' => 'required|numeric',
        ]);

        $position = $this->posService->create($data);
        $this->created($position);
    }

    public function update(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'  => 'required|min:2|max:100',
            'level' => 'required|numeric',
        ]);

        $position = $this->posService->update((int) $request->param('id'), $data);
        $this->success($position, 'Position updated');
    }

    public function destroy(Request $request): void
    {
        $this->posService->delete((int) $request->param('id'));
        $this->noContent();
    }
}
