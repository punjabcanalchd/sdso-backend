<?php

namespace Modules\Admin\Services\PageManagement;

use App\Models\Page;
use Modules\Admin\Repositories\PageManagement\PageRepository;

class PageService
{
    protected PageRepository $repository;

    public function __construct(PageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPages(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $pages = $this->repository->getAll($limit, $search, $sort_column, $sort_direction);
        $pages->getCollection()->transform(function ($page) {
            return $this->formatPage($page);
        });
        return $pages;
    }

    // public function getPages(int $limit)
    // {
    //     return Page::latest()->paginate($limit);
    // }

    // public function getPageByPublicId(string $publicId)
    // {
    //     $page = Page::findByPublicId($publicId);
    //     abort_if(! $page, 404, 'Page not found.');

    //     return $page;
    // }

    private function formatPage($page)
    {
        $english = $page->descriptions->firstWhere('language_id', 1);
        $punjabi = $page->descriptions->firstWhere('language_id', 2);

        return [
            'public_id' => $page->public_id,
            'name_en'   => $english?->title,
            'name_pb'   => $punjabi?->title,
            'created_at'=> $page->created_at,
            'status'    => $page->status,
        ];
    }
}
