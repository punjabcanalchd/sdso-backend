<?php

namespace Modules\Admin\Services\Others;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Admin\Repositories\Others\TranslationRepository;

class TranslationService
{
    public function __construct(
        protected TranslationRepository $repository
    ) {}

    /**
     * Get paginated translations formatted for admin list.
     */
    public function getPaginatedTranslations(
        int $limit = 25,
        $group = null,
        ?string $search = null,
        string $sortColumn = 'key_id',
        string $sortDirection = 'asc'
    ): LengthAwarePaginator {
        $paginated = $this->repository->getAll(
            $limit,
            $group,
            $search,
            $sortColumn,
            $sortDirection
        );

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

        return $paginated;
    }

    /**
     * Get single translation details by key_id.
     */
    public function getTranslationById(int|string $keyId): ?array
    {
        $translation = $this->repository->findById($keyId);
        if (!$translation) {
            return null;
        }

        $english = $translation->values->firstWhere('language_id', 1);
        $punjabi = $translation->values->firstWhere('language_id', 2);

        return [
            'key_id'          => $translation->key_id,
            'group'           => $translation->group,
            'translation_key' => $translation->translation_key,
            'en'              => $english?->translation ?? '',
            'pb'              => $punjabi?->translation ?? '',
        ];
    }

    /**
     * Create a new translation with English and Punjabi values.
     */
    public function createTranslation(array $data): Translation
    {
        $translation = $this->repository->create([
            'group'           => (int) $data['group'],
            'translation_key' => $data['translation_key'],
        ]);

        if (array_key_exists('en', $data) && $data['en'] !== null && $data['en'] !== '') {
            $this->repository->updateOrCreateValue($translation->key_id, 1, $data['en']);
        }

        if (array_key_exists('pb', $data) && $data['pb'] !== null && $data['pb'] !== '') {
            $this->repository->updateOrCreateValue($translation->key_id, 2, $data['pb']);
        }

        return $translation;
    }

    /**
     * Update an existing translation and its values.
     */
    public function updateTranslation(int|string $keyId, array $data): ?Translation
    {
        $translation = $this->repository->findById($keyId);
        if (!$translation) {
            return null;
        }

        $updateData = [];
        if (array_key_exists('group', $data)) {
            $updateData['group'] = $data['group'];
        }
        if (!empty($data['translation_key'])) {
            $updateData['translation_key'] = $data['translation_key'];
        }

        if (!empty($updateData)) {
            $this->repository->update($translation, $updateData);
        }

        if (array_key_exists('en', $data)) {
            $this->repository->updateOrCreateValue($keyId, 1, $data['en'] ?? '');
        }

        if (array_key_exists('pb', $data)) {
            $this->repository->updateOrCreateValue($keyId, 2, $data['pb'] ?? '');
        }

        return $translation;
    }

    /**
     * Batch update translations.
     */
    public function batchUpdateTranslations(array $translations): void
    {
        $this->repository->batchUpdate($translations);
    }
}
