<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\SmsTemplateService;
use Modules\Admin\Requests\Others\StoreSmsTemplateRequest;
use Modules\Admin\Requests\Others\UpdateSmsTemplateRequest;

class SmsTemplateController extends Controller
{
    use ApiResponse;
    protected SmsTemplateService $service;

    public function __construct(SmsTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Get paginated SMS templates with search and sorting.
     */
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
            'Sms Templates fetched successfully.'
        );
    }

    /**
     * Get single SMS template details.
     */
    public function show(Request $request, $id)
    {
        $status = $request->input('status', false);
        $this->service->updateStatus($id, $status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ], 200);
    }


    public function store(StoreSmsTemplateRequest $request)
    {

        $template = $this->service->store($request->validated());

        return $this->successResponse(
            $template,
            'Sms Template created successfully.',
            201
        );
    }

    public function update(UpdateSmsTemplateRequest $request, string $publicId) {

        $template = $this->service->update($request->validated(), $publicId);

        return $this->successResponse(
            $template,
            'Sms Template updated successfully.'
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

   
    public function destroy(string $public_id)
    {
        $this->service->delete($public_id);

        return response()->json([
            'success' => true,
            'message' => 'Sms Template deleted successfully.',
        ], 200);
    }
}
