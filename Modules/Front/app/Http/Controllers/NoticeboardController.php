<?php

namespace Modules\Front\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Front\Services\NoticeboardService;
use Illuminate\Support\Facades\Log;

class NoticeboardController extends Controller
{
    protected $noticeboardService;

    public function __construct(NoticeboardService $noticeboardService)
    {
        $this->noticeboardService = $noticeboardService;
    }

    /**
     * Get noticeboard details by ID and language ID.
     */
    public function show($id, $languageId): JsonResponse
    {
        try {
            // Fetch using the Service layer
            $noticeboard = $this->noticeboardService->getNoticeByIdAndLanguage($id, $languageId);

            if (!$noticeboard) {
                return response()->json([
                    'status' => false,
                    'message' => 'Noticeboard template not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Noticeboard details retrieved successfully.',
                'data' => $noticeboard,
            ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching noticeboard details: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the noticeboard details.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}