<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Front\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get active notifications / announcements.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $categoryId = $request->filled('category_id') ? (int) $request->get('category_id') : null;

        $data = $this->notificationService->getNotifications($limit, $categoryId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get home noticeboard category tabs with active notices.
     */
    public function tabs(Request $request): JsonResponse
    {
        $tabs = $this->notificationService->getNoticeBoardTabs();

        return response()->json([
            'success' => true,
            'data' => $tabs,
        ]);
    }

    /**
     * Get news ticker items.
     */
    public function ticker(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $categoryId = $request->filled('category_id') ? (int) $request->get('category_id') : null;

        $items = $this->notificationService->getTickerItems($limit, $categoryId);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }


public function noticeboard(int $languageId, int $id): JsonResponse
{
    try {
        $data = $this->notificationService->getNoticeboardPageData(
            $id,
            $languageId
        );

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Noticeboard category not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Noticeboard page data retrieved successfully.',
            'data' => $data,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching noticeboard data.',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
}
