<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Admin\Requests\Roles\AssignRoleRequest;
use Modules\Admin\Services\UserRoleService;

class UserRoleController extends Controller
{
    protected $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    public function assignRoleToUser(AssignRoleRequest $request, string $id)
    {
        $validated = $request->validated();
        $data = $this->userRoleService->assignRoleToUser($id, $validated['role']);
        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
            'data' => $data
        ], 200);
    }

    public function getUserRoleAndPermissions(string $id)
    {
        $data = $this->userRoleService->getUserRoleAndPermissions($id);

        return response()->json([
            'success' => true,
            'message' => 'User roles and permissions retrieved successfully.',
            'data' => $data
        ], 200);
    }
}
