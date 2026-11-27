
<?php

namespace Modules\Front\Repositories;

use App\Models\Page;
use App\Models\PageDescription;

class PageRepository
{
    /**
     * Get active page by page ID.
     */
    public function findActivePageById(int $pageId): ?Page
    {
        return Page::query()
            ->where('page_id', $pageId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Get page description by page ID and language ID.
     */
    public function findPageDescription(
        int $pageId,
        int $languageId
    ): ?PageDescription {
        return PageDescription::query()
            ->where('page_id', $pageId)
            ->where('language_id', $languageId)
            ->first();
    }

  
    /**
     * Get page with descriptions.
     */
    public function findPageWithDescriptions(int $pageId): ?Page
    {
        return Page::query()
            ->where('page_id', $pageId)
            ->where('status', 1)
           ->with([
            'pageDescription' => function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            }
        ])
        ->first();
    }
}
