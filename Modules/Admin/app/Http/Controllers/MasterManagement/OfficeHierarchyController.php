<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\OfficeHierarchyService;

class OfficeHierarchyController extends Controller
{
    use ApiResponse;

    protected OfficeHierarchyService $service;

    public function __construct(OfficeHierarchyService $service)
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

        $officeHierarchies = $this->service->getOfficeHierarchies($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $officeHierarchies,
            'Office Hierarchies fetched successfully.'
        );
    }

    /**
     * Get Office Hierarchy by id
     */
    public function show(string $publicId)
    {
        $officeHierarchy = $this->service->getOfficeHierarchy($publicId);

        if (! $officeHierarchy) {
            return $this->errorResponse(
                'Office Hierarchy not found.',
                404
            );
        }

        return $this->successResponse(
            $officeHierarchy,
            'Office Hierarchy fetched successfully.'
        );
    }
}
