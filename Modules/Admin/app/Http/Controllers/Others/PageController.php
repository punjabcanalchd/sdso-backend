<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Requests\Others\StorePageRequest;
use Modules\Admin\Requests\Others\UpdatePageRequest;
use Modules\Admin\Services\Others\PageService;

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

        $pages = $this->service->getPages($limit,
            $request->get('search'),
            $request->get('sort_column'),
            $request->get('sort_direction')
        );

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

    /**
     * Create page
     */
    public function store(StorePageRequest $request)
    {
        $page = $this->service->createPage($request->validated());

        return $this->successResponse(
            $page,
            'Page created successfully.',
            201
        );
    }

    /**
     * Update page
     */
    public function update(UpdatePageRequest $request, string $public_id)
    {
        // dd($public_id);
        // try {
        $page = $this->service->updatePage(
            $public_id,
            $request->validated()
        );

        return $this->successResponse(
            $public_id,
            'Page updated successfully.'
        );

        // } catch (\Throwable $e) {

        //     dd();
        // }
    }

    /**
     * Update page status
     */
    public function updateStatus(Request $request, string $public_id)
    {

        $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        $page = $this->service->updateStatus(
            $public_id,
            $request->boolean('status')
        );

        if (! $page) {
            return $this->errorResponse(
                'Page not found.',
                404
            );
        }

        return $this->successResponse(
            [
                'public_id' => $page->public_id,
                'status' => $page->status,
            ],
            'Page status updated successfully.'
        );
    }

    /**
     * Delete page
     */
    public function destroy(Page $page)
    {
        // Delete the page using the service
        $this->service->deletePage($page->public_id);

        return $this->successResponse(
            null,
            'Page deleted successfully.'
        );
    }
}
