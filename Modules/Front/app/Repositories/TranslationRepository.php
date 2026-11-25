<?php

namespace Modules\Front\Repositories;

use App\Models\Translation;
use Illuminate\Support\Collection;

class TranslationRepository
{
    public function __construct(
        protected Translation $model
    ) {}

    /**
     * Get all translations with their value for the specified language.
     * Also fetches English (language_id = 1) as a graceful fallback.
     */
    public function getTranslationsByLanguage(int $languageId): Collection
    {
        return $this->model
            ->with(['values' => function ($query) use ($languageId) {
                if ($languageId !== 1) {
                    $query->whereIn('language_id', [1, $languageId]);
                } else {
                    $query->where('language_id', 1);
                }
            }])
            ->get();
    }
}
