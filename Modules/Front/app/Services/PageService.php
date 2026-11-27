<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\PageRepository;
use Modules\Admin\Repositories\SettingRepository;

class PageService
{
    protected PageRepository $pageRepository;
    protected SettingRepository $settingRepository;

    public function __construct(
        PageRepository $pageRepository,
        SettingRepository $settingRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Get page details by page ID and language ID.
     */
    public function getPageByIdAndLanguage(
        int $pageId,
        int $languageId
    ): ?array {

        $page = $this->pageRepository->findActivePageById($pageId);

        if (!$page) {
            return null;
        }

        $description = $this->pageRepository->findPageDescription(
            $pageId,
            $languageId
        );

        if (!$description) {
            return null;
        }

        return [
            'page_id'          => $page->page_id,
            'public_id'        => $page->public_id,
            'slug'             => $page->slug,
            'page_type'        => $page->page_type,
            'title'            => $description->title,
            'description'      => $description->description,
            'meta_title'       => $description->meta_title,
            'meta_description' => $description->meta_description,
            'meta_keyword'     => $description->meta_keyword,
        ];
    }

    /**
     * Get website index text page according to language.
     */
    public function getIndexTextPage(int $languageId): ?array
    {
        $pageId = $this->settingRepository->getValue(
            'website_index_text_page_id'
        );

        if (empty($pageId) || !is_numeric($pageId)) {
            return null;
        }

        return $this->getPageByIdAndLanguage(
            (int) $pageId,
            $languageId
        );
    }
}