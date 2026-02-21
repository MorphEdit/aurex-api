<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\EmployeeService;

class EmployeeController extends BaseController
{
    private EmployeeService $empService;

    public function __construct()
    {
        $this->empService = new EmployeeService();
    }

    public function index(Request $request): void
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        $filters = [
            'search'      => $request->query('search'),
            'dept_id'     => $request->query('dept_id')     ? (int) $request->query('dept_id')     : null,
            'position_id' => $request->query('position_id') ? (int) $request->query('position_id') : null,
            'status'      => $request->query('status'),
        ];

        ['data' => $items, 'total' => $total] = $this->empService->list($page, $perPage, $filters);

        $this->paginated($items, $total, $page, $perPage);
    }

    public function show(Request $request): void
    {
        $employee = $this->empService->findById((int) $request->param('id'));
        $this->success($employee);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'emp_code'    => 'required|min:2|max:20',
            'full_name'   => 'required|min:2|max:150',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|max:20',
            'dept_id'     => 'nullable|numeric',
            'position_id' => 'nullable|numeric',
            'hire_date'   => 'required|date',
            'salary'      => 'required|numeric',
            'status'      => 'required|in:active,inactive,resigned',
        ]);

        $employee = $this->empService->create($data);
        $this->created($employee);
    }

    public function update(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'emp_code'    => 'required|min:2|max:20',
            'full_name'   => 'required|min:2|max:150',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|max:20',
            'dept_id'     => 'nullable|numeric',
            'position_id' => 'nullable|numeric',
            'hire_date'   => 'required|date',
            'salary'      => 'required|numeric',
            'status'      => 'required|in:active,inactive,resigned',
        ]);

        $employee = $this->empService->update((int) $request->param('id'), $data);
        $this->success($employee, 'Employee updated');
    }

    public function destroy(Request $request): void
    {
        $this->empService->delete((int) $request->param('id'));
        $this->noContent();
    }
}
