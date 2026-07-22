<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Requests\Menus\StoreMenuRequest;
use Modules\Admin\Requests\Menus\UpdateMenuRequest;
use Modules\Admin\Services\MenuService;
use Modules\Admin\Services\AdminPagePermissionService;

class MenuController extends Controller
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Get the pruned sidebar tree for the authenticated user.
     */
    public function sidebar(): JsonResponse
    {
        $sidebar = app(AdminPagePermissionService::class)->getSidebarTree();

        return response()->json([
            'success' => true,
            'message' => 'Sidebar menu retrieved successfully.',
            'data'    => $sidebar,
        ], 200);
    }

    /**
     * Get all menus.
     */
    public function index(): JsonResponse
    {
        $menus = $this->menuService->getAllMenus();

        return response()->json([
            'success' => true,
            'message' => 'Menus retrieved successfully.',
            'data'    => $menus,
        ], 200);
    }

    /**
     * Get a flat list of menus for the parent dropdown.
     */
    public function dropdown(): JsonResponse
    {
        $menus = $this->menuService->getDropdown();

        return response()->json([
            'success' => true,
            'message' => 'Menu dropdown retrieved successfully.',
            'data'    => $menus,
        ], 200);
    }

    /**
     * Create a new menu.
     */
    public function show(string $public_id): JsonResponse
    {
        $menu = $this->menuService->getMenu($public_id);

        return response()->json([
            'success' => true,
            'message' => 'Menu retrieved successfully.',
            'data'    => $menu,
        ], 200);
    }

    /**
     * Create a new menu.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->createMenu($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Menu created successfully.',
            'data'    => $menu,
        ], 201);
    }

    /**
     * Update an existing menu.
     */
    public function update(UpdateMenuRequest $request, string $public_id): JsonResponse
    {
        $menu = $this->menuService->updateMenu($public_id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully.',
            'data'    => $menu,
        ], 200);
    }

    /**
     * Toggle the status of a menu.
     */
    public function updateStatus(string $public_id): JsonResponse
    {
        $request = request();
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $menu = $this->menuService->updateStatus($public_id, (bool) $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Menu status updated successfully.',
            'data'    => $menu,
        ], 200);
    }

    /**
     * Delete a menu.
     */
    public function destroy(string $public_id): JsonResponse
    {
        $this->menuService->deleteMenu($public_id);

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully.',
        ], 200);
    }
}
