<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\TranslationService;

class TranslationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TranslationService $service
    ) {}

    /**
     * Get paginated translations list with optional group & search filtering
     */
    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $group = $request->get('group');
        $search = $request->get('search');
        $sortColumn = $request->get('sort_column', 'key_id');
        $sortDirection = $request->get('sort_direction', 'asc');

        $paginated = $this->service->getPaginatedTranslations(
            $limit,
            $group,
            $search,
            $sortColumn,
            $sortDirection
        );

        return $this->paginatedResponse($paginated, 'Translations fetched successfully.');
    }

    /**
     * Store a new translation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group'           => 'required|integer|in:1,2,3',
            'translation_key' => 'required|string|max:255|unique:translations,translation_key',
            'en'              => 'required|string',
            'pb'              => 'nullable|string',
        ]);

        $translation = $this->service->createTranslation($validated);

        return $this->successResponse($translation, 'Translation created successfully.', 201);
    }

    /**
     * Get single translation by key_id
     */
    public function show($keyId)
    {
        $data = $this->service->getTranslationById($keyId);

        if (!$data) {
            return $this->errorResponse('Translation not found.', 404);
        }

        return $this->successResponse($data, 'Translation details fetched successfully.');
    }

    /**
     * Update single translation by key_id
     */
    public function updateSingle(Request $request, $keyId)
    {
        $validated = $request->validate([
            'group'           => 'nullable|integer|in:1,2,3',
            'translation_key' => 'nullable|string|max:255|unique:translations,translation_key,' . $keyId . ',key_id',
            'en'              => 'nullable|string',
            'pb'              => 'nullable|string',
        ]);

        $translation = $this->service->updateTranslation($keyId, $validated);

        if (!$translation) {
            return $this->errorResponse('Translation key not found.', 404);
        }

        return $this->successResponse(null, 'Translation updated successfully.');
    }

    /**
     * Batch update translations
     */
    public function update(Request $request)
    {
        $translations = $request->input('translations', []);

        if (empty($translations) || !is_array($translations)) {
            return $this->errorResponse('No translations provided for update.', 422);
        }

        $this->service->batchUpdateTranslations($translations);

        return $this->successResponse(null, 'Translations updated successfully.');
    }
}
