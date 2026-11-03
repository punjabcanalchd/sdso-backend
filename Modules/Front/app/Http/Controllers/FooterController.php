<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Front\Services\FooterService;

class FooterController extends Controller
{
    public function __construct(
        private FooterService $footerService
    ) {}

    /**
     * Get dynamic footer configuration.
     */
    public function index(Request $request): JsonResponse
    {
        $languageId = (int) $request->get('language_id', 1);

        $data = $this->footerService->getFooterData($languageId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
