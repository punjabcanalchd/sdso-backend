<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Front\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function notifications(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $categoryId = $request->filled('category_id') ? (int) $request->get('category_id') : null;

        $data = $this->homeService->getNotifications($limit, $categoryId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function noticeBoardTabs(Request $request)
    {
        $tabs = $this->homeService->getNoticeBoardTabs();

        return response()->json([
            'success' => true,
            'data' => $tabs,
        ]);
    }
}
