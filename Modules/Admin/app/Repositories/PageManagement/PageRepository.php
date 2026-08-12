<?php

namespace Modules\Admin\Repositories\PageManagement;

use App\Models\Page;

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
        $query = Page::with(['englishDescription', 'punjabiDescription']);

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

    /* ------------------------------------------------------------------
     * UPDATE page
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Page
    {
        $page = $this->findByPublicId($publicId);
        $page->update($data);

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
