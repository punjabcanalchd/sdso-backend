<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Front\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->menuService->getActiveMenus(),
        ]);
    }
}
