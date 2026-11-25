<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\TranslationRepository;

class TranslationService
{
    public function __construct(
        protected TranslationRepository $repository
    ) {}

    /**
     * Get dictionary of all translations formatted for front-end consumption.
     */
    public function getTranslations(string $language = 'en'): array
    {
        $languageId = match (strtolower(trim($language))) {
            'pb', '2', 'pa' => 2,
            default         => 1,
        };

        $records = $this->repository->getTranslationsByLanguage($languageId);
        $dictionary = [];

        foreach ($records as $item) {
            $key = $item->translation_key;
            if (empty($key)) {
                continue;
            }

            // Find translation for the requested language
            $valObj = $item->values->firstWhere('language_id', $languageId);
            $translationText = $valObj?->translation;

            // Fallback to English if translation is missing or empty
            if (($translationText === null || $translationText === '') && $languageId !== 1) {
                $enObj = $item->values->firstWhere('language_id', 1);
                $translationText = $enObj?->translation ?? '';
            }

            $val = (string) ($translationText ?? '');

            // 1. Direct flat key lookup
            $dictionary[$key] = $val;

            // 2. Nested object tree lookup for dotted keys (e.g. 'NOTICEBOARD.TITLE')
            if (str_contains($key, '.')) {
                $parts = explode('.', $key);
                $curr = &$dictionary;
                foreach ($parts as $idx => $part) {
                    if ($idx === count($parts) - 1) {
                        $curr[$part] = $val;
                    } else {
                        if (!isset($curr[$part]) || !is_array($curr[$part])) {
                            $curr[$part] = [];
                        }
                        $curr = &$curr[$part];
                    }
                }
                unset($curr);
            }
        }

        return $dictionary;
    }
}
