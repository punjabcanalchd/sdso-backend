<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\SubDivisionService;
use Modules\Admin\Requests\MasterManagement\StoreSubDivisionRequest;
use Modules\Admin\Requests\MasterManagement\UpdateSubDivisionRequest;

class SubDivisionController extends Controller
{
    use ApiResponse;

    protected SubDivisionService $service;

    public function __construct(SubDivisionService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all SubDivisions
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

        $subdivisions = $this->service->getSubDivisions($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $subdivisions,
            'SubDivisions fetched successfully.'
        );
    }

    public function getAllSubDivisions(Request $request)
    {
        $subdivisions = $this->service->getAllSubDivisions();

        return $this->successResponse(
            $subdivisions,
            'Sub-Divisions fetched successfully.'
        );
    }

    /**
     * Get SubDivision by id
     */
    public function show(string $publicId)
    {
        $subdivision = $this->service->getSubDivision($publicId);

        if (! $subdivision) {
            return $this->errorResponse(
                'SubDivision not found.',
                404
            );
        }

        return $this->successResponse(
            $subdivision,
            'SubDivision fetched successfully.'
        );
    }

    /**
     * Get SubDivision by Division
     */
    public function getSubdivisionsByDivision(string $publicId)
    {
        $subdivisions = $this->service->getSubdivisionsByDivision($publicId);

        return $this->successResponse(
            $subdivisions,
            'SubDivisions fetched successfully.'
        );
    }

     /* ------------------------------------------------------------------
     * CREATE Sub Division
     * ---------------------------------------------------------------- */

    public function store(StoreSubDivisionRequest $request)
    {

        $subdivision = $this->service->createSubDivision($request->validated());

        return $this->successResponse(
            $subdivision,
            'Sub Division created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE Sub Division
     * ---------------------------------------------------------------- */

    public function update(UpdateSubDivisionRequest $request, string $publicId) 
    {
        $subdivision = $this->service->updateSubDivision($request->validated(), $publicId);

        return $this->successResponse(
            $subdivision,
            'Sub Division updated successfully.'
        );
    }
}
