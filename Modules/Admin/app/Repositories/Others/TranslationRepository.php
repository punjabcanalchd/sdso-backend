<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\Translation;
use App\Models\TranslationValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TranslationRepository
{
    protected Translation $model;

    public function __construct(Translation $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated translations with optional group & search filtering
     */
    public function getAll(
        int $perPage = 25,
        $group = null,
        ?string $search = null,
        string $sortColumn = 'key_id',
        string $sortDirection = 'asc'
    ): LengthAwarePaginator {
        $query = $this->model->with('values');

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
        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, strtolower($sortDirection) === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('key_id', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find translation by key_id
     */
    public function findById(int|string $keyId): ?Translation
    {
        return $this->model->with('values')->find($keyId);
    }

    /**
     * Create a new translation record
     */
    public function create(array $data): Translation
    {
        return $this->model->create([
            'group'           => (int) $data['group'],
            'translation_key' => trim($data['translation_key']),
        ]);
    }

    /**
     * Update an existing translation record
     */
    public function update(Translation $translation, array $data): Translation
    {
        if (array_key_exists('group', $data) && $data['group'] !== null) {
            $translation->group = (int) $data['group'];
        }
        if (!empty($data['translation_key'])) {
            $translation->translation_key = trim($data['translation_key']);
        }
        $translation->save();

        return $translation;
    }

    /**
     * Update or create a translation value for a specific language
     */
    public function updateOrCreateValue(int|string $keyId, int $languageId, string $value): TranslationValue
    {
        return TranslationValue::updateOrCreate(
            ['key_id' => $keyId, 'language_id' => $languageId],
            ['translation' => $value]
        );
    }

    /**
     * Batch update multiple translations
     */
    public function batchUpdate(array $translations): void
    {
        foreach ($translations as $item) {
            $keyId = $item['key_id'] ?? null;
            if (!$keyId) {
                continue;
            }

            if (array_key_exists('en', $item)) {
                $this->updateOrCreateValue($keyId, 1, $item['en'] ?? '');
            }

            if (array_key_exists('pb', $item)) {
                $this->updateOrCreateValue($keyId, 2, $item['pb'] ?? '');
            }
        }
    }
}
