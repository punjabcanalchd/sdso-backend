<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Admin\Requests\Roles\StoreRoleRequest;
use Modules\Admin\Requests\Roles\UpdateRoleRequest;
use Modules\Admin\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json([
            'success' => true,
            'message' => 'Roles retrieved successfully.',
            'data' => $roles
        ], 200);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role
        ], 201);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = $this->roleService->updateRole($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role
        ], 200);
    }
}

