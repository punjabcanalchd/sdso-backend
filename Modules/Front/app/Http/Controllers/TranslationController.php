<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Front\Services\TranslationService;

class TranslationController extends Controller
{
    public function __construct(
        protected TranslationService $service
    ) {}

    /**
     * Get dynamic translation dictionary for the specified language.
     * Accessible by public website for dynamic localization.
     */
    public function index(Request $request, ?string $language = 'en'): JsonResponse
    {
        $lang = $request->get('language', $language ?: 'en');
        $translations = $this->service->getTranslations($lang);

        return response()->json([
            'success' => true,
            'language' => $lang,
            'data' => $translations,
        ]);
    }
}
