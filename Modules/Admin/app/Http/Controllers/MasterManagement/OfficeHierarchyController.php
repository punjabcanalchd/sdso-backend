<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\OfficeHierarchyService;
use Modules\Admin\Requests\MasterManagement\StoreOfficeHierarchyRequest;
use Modules\Admin\Requests\MasterManagement\UpdateOfficeHierarchyRequest;

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

    public function getAllOfficeLevels(Request $request)
    {
        $officelevels = $this->service->getAllOfficeLevels();

        return $this->successResponse(
            $officelevels,
            'Data fetched successfully.'
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

     /* ------------------------------------------------------------------
     * CREATE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function store(StoreOfficeHierarchyRequest $request)
    {

        $officeHierarchy = $this->service->createOfficeHierarchy($request->validated());

        return $this->successResponse(
            $officeHierarchy,
            'Office Hierarchy created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function update(UpdateOfficeHierarchyRequest $request, string $publicId) {

        $officeHierarchy = $this->service->updateOfficeHierarchy($request->validated(), $publicId);

        return $this->successResponse(
            $officeHierarchy,
            'Office Hierarchy updated successfully.'
        );
    }
}
