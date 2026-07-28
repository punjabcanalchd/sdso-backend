<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Admin\Requests\Roles\StoreRoleRequest;
use Modules\Admin\Requests\Roles\UpdateRoleRequest;
use Modules\Admin\Services\RoleService;
use App\Traits\ApiResponse;

class RoleController extends Controller
{
    protected $roleService;
    use ApiResponse;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit');
        $maxLimit = config('pagination.max_limit');
        $limit = (int) $request->input('per_page',$defaultLimit);
        $search = $request->input('search');
        $sort_column =  $request->input('sort_column');
        $sort_direction =  $request->input('sort_direction');
        $limit = min($limit, $maxLimit);

        $limit = max($limit, 1);
        $roles = $this->roleService->getAllRoles($limit, $search, $sort_column, $sort_direction);
        return $this->paginatedResponse(
            $roles,
            'Roles fetched successfully.'
        );
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

