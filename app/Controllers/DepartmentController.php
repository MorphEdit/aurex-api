<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\DepartmentService;

class DepartmentController extends BaseController
{
    private DepartmentService $deptService;

    public function __construct()
    {
        $this->deptService = new DepartmentService();
    }

    public function index(Request $request): void
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        $search  = $request->query('search');

        ['data' => $items, 'total' => $total] = $this->deptService->list($page, $perPage, $search);

        $this->paginated($items, $total, $page, $perPage);
    }

    public function show(Request $request): void
    {
        $dept = $this->deptService->findById((int) $request->param('id'));
        $this->success($dept);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'        => 'required|min:2|max:100',
            'description' => 'nullable|max:500',
        ]);

        $dept = $this->deptService->create($data);
        $this->created($dept);
    }

    public function update(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'        => 'required|min:2|max:100',
            'description' => 'nullable|max:500',
        ]);

        $dept = $this->deptService->update((int) $request->param('id'), $data);
        $this->success($dept, 'Department updated');
    }

    public function destroy(Request $request): void
    {
        $this->deptService->delete((int) $request->param('id'));
        $this->noContent();
    }
}
