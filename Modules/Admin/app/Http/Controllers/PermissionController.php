<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Admin\Services\ModulePermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(ModulePermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $grouped = $this->permissionService->getGroupedPermissions();

        return response()->json([
            'data' => $grouped
        ], 200);
    }

    public function getTree()
    {
        $tree = $this->permissionService->getTree();

        return response()->json([
            'success' => true,
            'message' => 'Permissions tree retrieved successfully.',
            'data' => $tree
        ], 200);
    }

    public function getRoleTree(string $roleId)
    {
        $tree = $this->permissionService->getRoleTree($roleId);

        return response()->json([
            'success' => true,
            'message' => 'Role permissions tree retrieved successfully.',
            'data' => $tree
        ], 200);
    }

    public function syncRolePermissions(Request $request, string $roleId)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->permissionService->syncRolePermissions($roleId, $request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions synced successfully.'
        ], 200);
    }

    /**
     * GET /admin/permissions/allowed-actions/{slug}
     * Returns the list of actions the authenticated user can perform on the given page slug.
     */
    public function allowedActions(string $slug, \Modules\Admin\Services\AdminPagePermissionService $permService)
    {
        $user = auth('api')->user();
        $allowed = $permService->getAllowedActionsForUser($user, $slug);
        return response()->json([
            'slug'    => $slug,
            'allowed' => $allowed,
        ], 200);
    }
}
