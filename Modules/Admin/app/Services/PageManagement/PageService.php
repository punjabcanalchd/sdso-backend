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
        $states = $this->repository->getAll($limit, $search, $sort_column, $sort_direction);
        dd($states);
        $states->getCollection()->transform(function ($state) {
            return $this->formatPage($state);
        });
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
}
