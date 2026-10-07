<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\EmailTemplateService;
use Modules\Admin\Requests\Others\StoreEmailTemplateRequest;
use Modules\Admin\Requests\Others\UpdateEmailTemplateRequest;

class EmailTemplateController extends Controller
{
    use ApiResponse;

    protected EmailTemplateService $service;

    public function __construct(EmailTemplateService $service)
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
            'Email Templates fetched successfully.'
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


    public function store(StoreEmailTemplateRequest $request)
    {

        $template = $this->service->store($request->validated());

        return $this->successResponse(
            $template,
            'Email Template created successfully.',
            201
        );
    }

    public function update(UpdateEmailTemplateRequest $request, string $publicId) {

        $template = $this->service->update($request->validated(), $publicId);

        return $this->successResponse(
            $template,
            'Email Template updated successfully.'
        );
    }

   
    public function destroy(string $public_id)
    {
        $this->service->delete($public_id);

        return response()->json([
            'success' => true,
            'message' => 'Email Template deleted successfully.',
        ], 200);
    }
}
