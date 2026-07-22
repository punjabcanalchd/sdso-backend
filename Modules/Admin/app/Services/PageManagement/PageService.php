<?php

namespace Modules\Admin\Services\PageManagement;

use App\Models\Page;

class PageService
{
    public function getPages(int $limit)
    {
        return Page::latest()->paginate($limit);
    }

    public function getPageByPublicId(string $publicId)
    {
        $page = Page::findByPublicId($publicId);
        abort_if(!$page, 404, 'Page not found.');
        return $page;
    }
}
