<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\UserService;

class UserController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function index(Request $request): void
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        $search  = $request->query('search');

        ['data' => $users, 'total' => $total] = $this->userService->list($page, $perPage, $search);

        $this->paginated($users, $total, $page, $perPage);
    }

    public function show(Request $request): void
    {
        $user = $this->userService->findById((int) $request->param('id'));
        $this->success($user);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email',
            'password' => 'required|min:8',
            'role'     => 'required|in:super_admin,admin',
        ]);

        $user = $this->userService->create($data);
        $this->created($user);
    }

    public function update(Request $request): void
    {
        $data = $this->validate($request->all(), [
            'name'  => 'required|min:2|max:100',
            'email' => 'required|email',
            'role'  => 'required|in:super_admin,admin',
        ]);

        $user = $this->userService->update((int) $request->param('id'), $data);
        $this->success($user, 'User updated');
    }

    public function destroy(Request $request): void
    {
        $this->userService->delete((int) $request->param('id'));
        $this->noContent();
    }
}
