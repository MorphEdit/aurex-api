<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

// ── Public ────────────────────────────────────────────────────────────────────
$router->post('/api/v1/auth/login', 'AuthController@login');

// ── Protected (JWT required) ──────────────────────────────────────────────────
$router->group('/api/v1', [new AuthMiddleware()], function ($router) {

    // Auth
    $router->get('/auth/me',      'AuthController@me');
    $router->post('/auth/logout', 'AuthController@logout');

    // ── Users — super_admin only ──────────────────────────────────────────────
    $router->group('/users', [new RoleMiddleware(['super_admin'])], function ($router) {
        $router->get('',        'UserController@index');
        $router->post('',       'UserController@store');
        $router->get('/{id}',   'UserController@show');
        $router->put('/{id}',   'UserController@update');
        $router->delete('/{id}','UserController@destroy');
    });

    // ── Departments — admin + super_admin ─────────────────────────────────────
    $router->group('/departments', [new RoleMiddleware(['admin', 'super_admin'])], function ($router) {
        $router->get('',        'DepartmentController@index');
        $router->post('',       'DepartmentController@store');
        $router->get('/{id}',   'DepartmentController@show');
        $router->put('/{id}',   'DepartmentController@update');
        $router->delete('/{id}','DepartmentController@destroy');
    });

    // ── Positions — admin + super_admin ───────────────────────────────────────
    $router->group('/positions', [new RoleMiddleware(['admin', 'super_admin'])], function ($router) {
        $router->get('',        'PositionController@index');
        $router->post('',       'PositionController@store');
        $router->get('/{id}',   'PositionController@show');
        $router->put('/{id}',   'PositionController@update');
        $router->delete('/{id}','PositionController@destroy');
    });

    // ── Employees — admin + super_admin ──────────────────────────────────────
    $router->group('/employees', [new RoleMiddleware(['admin', 'super_admin'])], function ($router) {
        $router->get('',        'EmployeeController@index');
        $router->post('',       'EmployeeController@store');
        $router->get('/{id}',   'EmployeeController@show');
        $router->put('/{id}',   'EmployeeController@update');
        $router->delete('/{id}','EmployeeController@destroy');
    });
});
