<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\ClientQueryService;

class ClientQueryController extends Controller
{
    use ApiResponse;

    protected ClientQueryService $service;

    public function __construct(ClientQueryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $defaultLimit = config('pagination.default_limit', 25);
        $maxLimit = config('pagination.max_limit', 100);

        $limit = (int) $request->input('per_page', $defaultLimit);
        $limit = min(max($limit, 1), $maxLimit);

        $search = $request->input('search');
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $status = $request->input('status');

        $queries = $this->service->getQueries($limit, $search, $sortColumn, $sortDirection, $status);
        $counts = $this->service->getStatusCounts();

        $response = $this->paginatedResponse($queries, 'Client queries fetched successfully.');
        $data = $response->getData(true);
        $data['status_counts'] = $counts;

        return response()->json($data, 200);
    }

    public function statusCounts(): JsonResponse
    {
        $counts = $this->service->getStatusCounts();
        return $this->successResponse($counts, 'Status counts fetched successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $query = $this->service->getQueryById($id);

        if (!$query) {
            return $this->errorResponse('Client query not found.', 404);
        }

        return $this->successResponse($query, 'Client query details fetched successfully.');
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:0,1,2'],
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        $query = $this->service->updateStatus($id, (int) $validated['status'], $validated['remark'] ?? null);

        if (!$query) {
            return $this->errorResponse('Client query not found.', 404);
        }

        return $this->successResponse($query, 'Query status updated successfully.');
    }

    public function addRemark(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'remark' => ['required', 'string', 'max:2000'],
            'status' => ['nullable', 'integer', 'in:0,1,2'],
        ]);

        $status = isset($validated['status']) ? (int) $validated['status'] : null;
        $query = $this->service->addRemark($id, $validated['remark'], $status);

        if (!$query) {
            return $this->errorResponse('Client query not found.', 404);
        }

        return $this->successResponse($query, 'Remark added successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->service->deleteQuery($id);

        if (!$deleted) {
            return $this->errorResponse('Client query not found or could not be deleted.', 404);
        }

        return $this->successResponse(null, 'Client query deleted successfully.');
    }
}
