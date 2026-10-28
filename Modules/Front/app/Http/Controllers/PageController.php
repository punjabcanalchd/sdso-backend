<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Front\Services\PageService;

class PageController extends Controller
{
    public function __construct(
        private PageService $pageService
    ) {}

    // Get a single page by page ID and language ID
    public function show( int $pageId, int $languageId ): JsonResponse { 

        $page = $this->pageService->getPageByIdAndLanguage($pageId, $languageId);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for the selected language.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }
}