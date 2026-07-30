<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\DesignationService;

class DesignationController extends Controller
{
    use ApiResponse;

    protected DesignationService $service;

    public function __construct(DesignationService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all Office Hierarchies
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

        $designations = $this->service->getDesignations($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $designations,
            'Designations fetched successfully.'
        );
    }

    /**
     * Get Office Hierarchy by id
     */
    public function show(string $publicId)
    {
        $designation = $this->service->getDesignation($publicId);

        if (! $designation) {
            return $this->errorResponse(
                'Office Hierarchy not found.',
                404
            );
        }

        return $this->successResponse(
            $designation,
            'Designation fetched successfully.'
        );
    }
}
