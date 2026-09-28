<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\CategoryTemplateService;

class NoticeboardCategoryController extends Controller
{
    use ApiResponse;

    protected CategoryTemplateService $service;

    public function __construct(CategoryTemplateService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit', 25);
        $maxLimit = config('pagination.max_limit', 100);

        $limit = (int) $request->get('per_page', $defaultLimit);
        $limit = min($limit, $maxLimit);
        $limit = max($limit, 1);

        $categories = $this->service->getCategories(
            $limit,
            $request->get('search'),
            $request->get('sort_column', 'template_id'),
            $request->get('sort_direction', 'desc')
        );

        return $this->paginatedResponse($categories, 'Noticeboard categories fetched successfully.');
    }

    public function show(string $id)
    {
        $category = $this->service->getCategoryByPublicId($id);

        if (!$category) {
            return $this->errorResponse('Noticeboard category not found.', 404);
        }

        return $this->successResponse($category, 'Noticeboard category fetched successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        $category = $this->service->createCategory([
            'name_en'              => $validated['name_en'],
            'name_pb'              => $validated['name_pb'],
            'display_on_home_page' => $request->boolean('display_on_home_page'),
            'status'               => $request->has('status') ? $request->boolean('status') : true,
        ]);

        return $this->successResponse(
            $this->service->formatCategory($category),
            'Noticeboard category created successfully.',
            201
        );
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        $category = $this->service->updateCategory($id, [
            'name_en'              => $validated['name_en'],
            'name_pb'              => $validated['name_pb'],
            'display_on_home_page' => $request->boolean('display_on_home_page'),
            'status'               => $request->boolean('status'),
        ]);

        if (!$category) {
            return $this->errorResponse('Noticeboard category not found.', 404);
        }

        return $this->successResponse(
            $this->service->formatCategory($category),
            'Noticeboard category updated successfully.'
        );
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required'],
        ]);

        $category = $this->service->updateStatus($id, $request->boolean('status'));

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        return $this->successResponse($category, 'Status updated successfully.');
    }

    public function updateDisplayOnHome(Request $request, string $id)
    {
        $request->validate([
            'display_on_home_page' => ['required'],
        ]);

        $category = $this->service->updateDisplayOnHome($id, $request->boolean('display_on_home_page'));

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        return $this->successResponse($category, 'Display on home page updated successfully.');
    }
}
