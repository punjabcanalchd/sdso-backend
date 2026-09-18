<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\TranslationValue;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    use ApiResponse;

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

        $query = Translation::with('values');

        // Filter by group if provided
        if ($group !== null && $group !== '' && $group !== 'all') {
            $query->where('group', (int) $group);
        }

        // Filter by search string
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('translation_key', 'ilike', "%{$search}%")
                  ->orWhereHas('values', function ($vq) use ($search) {
                      $vq->where('translation', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['key_id', 'group', 'translation_key'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, strtolower($sortDirection) === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('key_id', 'asc');
        }

        $paginated = $query->paginate($limit);

        $paginated->getCollection()->transform(function ($item) {
            $english = $item->values->firstWhere('language_id', 1);
            $punjabi = $item->values->firstWhere('language_id', 2);

            return [
                'key_id'          => $item->key_id,
                'group'           => $item->group,
                'translation_key' => $item->translation_key,
                'en'              => $english?->translation ?? '',
                'pb'              => $punjabi?->translation ?? '',
            ];
        });

        return $this->paginatedResponse($paginated, 'Translations fetched successfully.');
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

        foreach ($translations as $item) {
            $keyId = $item['key_id'] ?? null;
            if (!$keyId) continue;

            if (array_key_exists('en', $item)) {
                TranslationValue::updateOrCreate(
                    ['key_id' => $keyId, 'language_id' => 1],
                    ['translation' => $item['en'] ?? '']
                );
            }

            if (array_key_exists('pb', $item)) {
                TranslationValue::updateOrCreate(
                    ['key_id' => $keyId, 'language_id' => 2],
                    ['translation' => $item['pb'] ?? '']
                );
            }
        }

        return $this->successResponse(null, 'Translations updated successfully.');
    }

    /**
     * Update single translation by key_id
     */
    public function updateSingle(Request $request, $keyId)
    {
        $translation = Translation::find($keyId);
        if (!$translation) {
            return $this->errorResponse('Translation key not found.', 404);
        }

        if ($request->has('en')) {
            TranslationValue::updateOrCreate(
                ['key_id' => $keyId, 'language_id' => 1],
                ['translation' => $request->input('en', '')]
            );
        }

        if ($request->has('pb')) {
            TranslationValue::updateOrCreate(
                ['key_id' => $keyId, 'language_id' => 2],
                ['translation' => $request->input('pb', '')]
            );
        }

        return $this->successResponse(null, 'Translation updated successfully.');
    }
}
