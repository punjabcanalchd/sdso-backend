<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\CategoryTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NoticeboardCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $search = $request->get('search');
        $sortColumn = $request->get('sort_column', 'template_id');
        $sortDirection = $request->get('sort_direction', 'desc');

        $query = CategoryTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'display_on_home_page', 'created_at', 'template_id'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, strtolower($sortDirection) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('template_id', 'desc');
        }

        $paginated = $query->paginate($limit);

        $paginated->getCollection()->transform(function ($item) {
            $english = $item->descriptions->firstWhere('language_id', 1);
            $punjabi = $item->descriptions->firstWhere('language_id', 2);

            return [
                'id'                   => $item->public_id ?? (string) $item->template_id,
                'template_id'          => $item->template_id,
                'name'                 => $item->name,
                'name_en'              => $english?->message ?? $item->name,
                'name_pb'              => $punjabi?->message ?? '',
                'display_on_home_page' => (bool) $item->display_on_home_page,
                'status'               => (bool) $item->status,
                'created_at'           => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $this->paginatedResponse($paginated, 'Noticeboard categories fetched successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $category = CategoryTemplate::findByPublicId($id) ?? CategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        $category->status = (bool) $request->input('status', false);
        $category->save();

        return $this->successResponse($category, 'Status updated successfully.');
    }

    public function updateDisplayOnHome(Request $request, $id)
    {
        $category = CategoryTemplate::findByPublicId($id) ?? CategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found.', 404);
        }

        $category->display_on_home_page = (bool) $request->input('display_on_home_page', false);
        $category->save();

        return $this->successResponse($category, 'Display on home page updated successfully.');
    }
}
