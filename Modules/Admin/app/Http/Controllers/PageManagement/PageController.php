<?php

namespace Modules\Admin\Http\Controllers\PageManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\PageManagement\PageService;

class PageController extends Controller
{
    use ApiResponse;

    protected PageService $service;

    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all pages
     */
    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit');
        $maxLimit = config('pagination.max_limit');

        $limit = (int) $request->get('limit', $defaultLimit);
        $limit = min($limit, $maxLimit);
        $limit = max($limit, 1);

        $pages = $this->service->getPages($limit);

        return $this->paginatedResponse(
            $pages,
            'Pages fetched successfully.'
        );
    }

    /**
     * Get page by slug
     */
    public function show(string $publicId)
    {
        $page = $this->service->getPageByPublicId($publicId);

        if (! $page) {
            return $this->errorResponse(
                'Page not found.',
                404
            );
        }

        return $this->successResponse(
            $page,
            'Page fetched successfully.'
        );
    }
}
