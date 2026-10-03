<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\MediaCategoryService;

class MediaCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected MediaCategoryService $service)
    {
    }

    /**
     * Get paginated media categories.
     */
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
            $request->get('sort_column', 'mediacat_id'),
            $request->get('sort_direction', 'desc')
        );

        return $this->paginatedResponse($categories, 'Media categories fetched successfully.');
    }

    /**
     * Get media category dropdown items.
     */
    public function dropdown()
    {
        $categories = $this->service->getDropdown();
        return $this->successResponse($categories, 'Media category dropdown fetched successfully.');
    }

    /**
     * Get media category details by ID.
     */
    public function show(string|int $id)
    {
        $category = $this->service->getCategoryById($id);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        return $this->successResponse($category, 'Media category fetched successfully.');
    }

    /**
     * Store new media category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'description_en'       => ['nullable', 'string'],
            'description_pb'       => ['nullable', 'string'],
            'category_image'       => ['nullable'],
            'parent_id'            => ['nullable'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        $file = $request->file('category_image');
        $existingImage = is_string($request->input('category_image')) ? $request->input('category_image') : null;

        $created = $this->service->createCategory($validated, $file, $existingImage);

        return $this->successResponse($created, 'Media category created successfully.', 201);
    }

    /**
     * Update existing media category.
     */
    public function update(Request $request, string|int $id)
    {
        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'description_en'       => ['nullable', 'string'],
            'description_pb'       => ['nullable', 'string'],
            'category_image'       => ['nullable'],
            'parent_id'            => ['nullable'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        $file = $request->file('category_image');
        $existingImage = is_string($request->input('category_image')) ? $request->input('category_image') : null;

        $updated = $this->service->updateCategory($id, $validated, $file, $existingImage);

        if (!$updated) {
            return $this->errorResponse('Media category not found.', 404);
        }

        return $this->successResponse($updated, 'Media category updated successfully.');
    }

    /**
     * Toggle status.
     */
    public function updateStatus(Request $request, string|int $id)
    {
        $status = (bool) $request->input('status', false);
        $category = $this->service->updateStatus($id, $status);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        return $this->successResponse($category, 'Status updated successfully.');
    }

    /**
     * Toggle display on home page.
     */
    public function updateDisplayOnHome(Request $request, string|int $id)
    {
        $display = (bool) $request->input('display_on_home_page', false);
        $category = $this->service->updateDisplayOnHome($id, $display);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        return $this->successResponse($category, 'Display on home page updated successfully.');
    }
}
