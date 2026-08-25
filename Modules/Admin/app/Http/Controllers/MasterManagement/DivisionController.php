<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\DivisionService;
use Modules\Admin\Requests\MasterManagement\StoreDivisionRequest;
use Modules\Admin\Requests\MasterManagement\UpdateDivisionRequest;

class DivisionController extends Controller
{
    use ApiResponse;

    protected DivisionService $service;

    public function __construct(DivisionService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all Divisions
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

        $divisions = $this->service->getDivisions($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $divisions,
            'Divisions fetched successfully.'
        );
    }

    public function getAllDivisions(Request $request)
    {
        $divisions = $this->service->getAllDivisions();

        return $this->successResponse(
            $divisions,
            'Divisions fetched successfully.'
        );
    }

    /**
     * Get Division by id
     */
    public function show(string $publicId)
    {
        $division = $this->service->getDivision($publicId);

        if (! $division) {
            return $this->errorResponse(
                'Division not found.',
                404
            );
        }

        return $this->successResponse(
            $division,
            'Division fetched successfully.'
        );
    }

    /**
     * Get Division by Circle
     */
    public function getDivisionsByCircle(string $publicId)
    {
        $divisions = $this->service->getDivisionsByCircle($publicId);

        return $this->successResponse(
            $divisions,
            'Divisions fetched successfully.'
        );
    }

     /* ------------------------------------------------------------------
     * CREATE Division
     * ---------------------------------------------------------------- */

    public function store(StoreDivisionRequest $request)
    {

        $division = $this->service->createDivision($request->validated());

        return $this->successResponse(
            $division,
            'Division created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE Division
     * ---------------------------------------------------------------- */

    public function update(UpdateDivisionRequest $request, string $publicId) {

        $division = $this->service->updateDivision($request->validated(), $publicId);

        return $this->successResponse(
            $division,
            'Division updated successfully.'
        );
    }
}
