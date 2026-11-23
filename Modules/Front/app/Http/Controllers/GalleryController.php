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

    /**
     * Get full Gallery page data (albums, categories, and photos).
     */
    public function showGallery(Request $request, string|int|null $id = null): JsonResponse
    {
        $langParam = $request->get('language_id', $request->get('lang', 1));
        $languageId = match (strtolower((string) $langParam)) {
            'pb', '2' => 2,
            default   => 1,
        };

        $categoryId = $id ?: $request->get('category_id');

        $data = $this->galleryService->getGalleryPageData($languageId, $categoryId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

