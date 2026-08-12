<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\Page;
use App\Models\PageDescription;

class PageRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Pages
     * ---------------------------------------------------------------- */

    public function getAll(
        int $limit,
        ?string $search,
        ?string $sort_column,
        ?string $sort_direction
    ) {
        $query = Page::with(['descriptions']);

        if (! empty($search)) {
            $query->whereHas('descriptions', function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $sort_direction = strtolower($sort_direction ?? 'asc');

        if (! in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'asc';
        }

        $query->orderBy($sort_column ?? 'page_id', $sort_direction);

        return $query->paginate($limit);
    }
    /* ------------------------------------------------------------------
     * GET SINGLE page BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Page
    {
        $page = Page::findByPublicId($publicId);
        abort_if(! $page, 404, 'page not found.');

        return $page;
    }

    /* ------------------------------------------------------------------
     * CREATE page
     * ---------------------------------------------------------------- */

    public function create(array $data): Page
    {
        return Page::create($data);
    }

    public function createDescriptions(int $pageId, array $translations): void {
        foreach ($translations['title'] as $languageId => $title) {
            PageDescription::create([
                'page_id' => $pageId,
                'language_id' => $languageId,
                'title' => $title,
                'description' => $translations['description'][$languageId] ?? null,
                'meta_title' => $translations['meta_title'][$languageId] ?? null,
                'meta_description' => $translations['meta_description'][$languageId] ?? null,
                'meta_keyword' => $translations['meta_keyword'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE page
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Page
    {
        $page = $this->findByPublicId($publicId);
        $page->update($data);

        return $page;
    }

    public function updatePageWithDescriptions(Page $page, array $pageData, array $descriptions): Page {

        $page->update($pageData);
        
        PageDescription::where('page_id', $page->page_id)->delete();

        foreach ($descriptions as $description) {

            PageDescription::create([
                'page_id' => $page->page_id,
                'language_id' => $description['language_id'],
                'title' => $description['title'],
                'description' => $description['description'],
                'meta_title' => $description['meta_title'],
                'meta_description' => $description['meta_description'],
                'meta_keyword' => $description['meta_keyword'],
            ]);
        }

        return $page;
    }

    /* ------------------------------------------------------------------
     * DELETE page
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $page = $this->findByPublicId($publicId);

        return $page->delete();
    }
}
