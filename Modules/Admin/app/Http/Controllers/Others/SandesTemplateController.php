<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\SandesTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\SandesTemplateService;
use Modules\Admin\Requests\Others\StoreSandesTemplateRequest;
use Modules\Admin\Requests\Others\UpdateSandesTemplateRequest;

class SandesTemplateController extends Controller
{
    use ApiResponse;
    protected SandesTemplateService $service;

    public function __construct(SandesTemplateService $service)
    {
        $this->service = $service;
    }

   public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit');

        $maxLimit = config('pagination.max_limit');

        $limit = (int) $request->input('per_page',$defaultLimit);
        $search = $request->input('search');
        $sort_column =  $request->input('sort_column');
        $sort_direction =  $request->input('sort_direction');

        $limit = min($limit, $maxLimit);

        $limit = max($limit, 1);

        $template = $this->service->getAll($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $template,
            'Sandes Templates fetched successfully.'
        );
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status', false);
        $this->service->updateStatus($id, $status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ], 200);
    }


    public function store(StoreSandesTemplateRequest $request)
    {

        $template = $this->service->store($request->validated());

        return $this->successResponse(
            $template,
            'Sandes Template created successfully.',
            201
        );
    }

    public function update(UpdateSandesTemplateRequest $request, string $publicId) {

        $template = $this->service->update($request->validated(), $publicId);

        return $this->successResponse(
            $template,
            'Sandes Template updated successfully.'
        );
    }

   
    public function destroy(string $public_id)
    {
        $this->service->delete($public_id);

        return response()->json([
            'success' => true,
            'message' => 'Sandes Template deleted successfully.',
        ], 200);
    }
}
