<?php

namespace Modules\Front\Services;

use App\Models\Page;
use App\Models\PageDescription;

class PageService
{
    public function getPageByIdAndLanguage(int $pageId, int $languageId): ?array {
        
        $page = Page::query()
            ->where('page_id', $pageId)
            ->where('status', 1)
            ->first();

        if (!$page) {
            return null;
        }

        $description = PageDescription::query()
            ->where('page_id', $pageId)
            ->where('language_id', $languageId)
            ->first();

        if (!$description) {
            return null;
        }

        return [
            'page_id' => $page->page_id,
            'public_id' => $page->public_id,
            'slug' => $page->slug,
            'page_type' => $page->page_type,
            'title' => $description->title,
            'description' => $description->description,
            'meta_title' => $description->meta_title,
            'meta_description' => $description->meta_description,
            'meta_keyword' => $description->meta_keyword,
        ];
    }
}