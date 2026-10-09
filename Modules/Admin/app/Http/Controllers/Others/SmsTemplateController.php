<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\SmsTemplateService;

class SmsTemplateController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SmsTemplateService $service
    ) {}

    /**
     * Get paginated SMS templates with search and sorting.
     */
    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $search = $request->get('search');
        $sortColumn = $request->get('sort_column', 'template_id');
        $sortDirection = $request->get('sort_direction', 'desc');

        $paginated = $this->service->getPaginatedTemplates(
            $limit,
            $search,
            $sortColumn,
            $sortDirection
        );

        return $this->paginatedResponse($paginated, 'SMS templates fetched successfully.');
    }

    /**
     * Get single SMS template details.
     */
    public function show($id)
    {
        $template = $this->service->getTemplateById($id);

        if (!$template) {
            return $this->errorResponse('SMS template not found.', 404);
        }

        return $this->successResponse($template, 'SMS template details fetched successfully.');
    }

    /**
     * Store a new SMS template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'templateid'  => 'required|string|max:255',
            'status'      => 'required',
            'message_en'  => 'required|string',
            'message_pb'  => 'nullable|string',
        ]);

        $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $validated['status'];

        $template = $this->service->createTemplate($validated);

        return $this->successResponse($template, 'SMS template created successfully.', 201);
    }

    /**
     * Update an existing SMS template.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'templateid'  => 'required|string|max:255',
            'status'      => 'required',
            'message_en'  => 'required|string',
            'message_pb'  => 'nullable|string',
        ]);

        $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $validated['status'];

        $template = $this->service->updateTemplate($id, $validated);

        if (!$template) {
            return $this->errorResponse('SMS template not found.', 404);
        }

        return $this->successResponse($template, 'SMS template updated successfully.');
    }

    /**
     * Update status of an SMS template.
     */
    public function updateStatus(Request $request, $id)
    {
        $status = (bool) $request->input('status', false);
        $updated = $this->service->updateStatus($id, $status);

        if (!$updated) {
            return $this->errorResponse('SMS template not found.', 404);
        }

        return $this->successResponse(null, 'Status updated successfully.');
    }
}
