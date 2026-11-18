<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Front\Services\GalleryService;

class GalleryController extends Controller
{
    public function __construct(
        private GalleryService $galleryService
    ) {}

    /**
     * Get Work Gallery data for the front/home page.
     */
    public function index(Request $request): JsonResponse
    {
        $languageId = (int) $request->get('language_id', 1);
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 30;

        $data = $this->galleryService->getWorkGalleryData($languageId, $limit);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

