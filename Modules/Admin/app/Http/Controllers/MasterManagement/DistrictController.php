<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\DistrictService;
use Modules\Admin\Requests\MasterManagement\StoreDistrictRequest;
use Modules\Admin\Requests\MasterManagement\UpdateDistrictRequest;

class DistrictController extends Controller
{
    use ApiResponse;

    protected DistrictService $service;

    public function __construct(DistrictService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all Districts
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

        $districts = $this->service->getDistricts($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $districts,
            'Districts fetched successfully.'
        );
    }

    public function getAllDistricts(Request $request)
    {
       
        $districts = $this->service->getAllDistricts();

        return $this->successResponse(
            $districts,
            'Districts fetched successfully.'
        );
    }


    /**
     * Get District by id
     */
    public function show(string $publicId)
    {
        $district = $this->service->getDistrict($publicId);

        if (! $district) {
            return $this->errorResponse(
                'District not found.',
                404
            );
        }

        return $this->successResponse(
            $district,
            'District fetched successfully.'
        );
    }

    /**
     * Get Districts by State
     */
    public function getDistrictsByState(string $publicId)
    {
        $districts = $this->service->getDistrictsByState($publicId);

        return $this->successResponse(
            $districts,
            'Districts fetched successfully.'
        );
    }


     /* ------------------------------------------------------------------
     * CREATE District
     * ---------------------------------------------------------------- */

    public function store(StoreDistrictRequest $request)
    {

        $district = $this->service->createDistrict($request->validated());

        return $this->successResponse(
            $district,
            'District created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE District
     * ---------------------------------------------------------------- */

    public function update(UpdateDistrictRequest $request, string $publicId) {

        $district = $this->service->updateDistrict($request->validated(), $publicId);

        return $this->successResponse(
            $district,
            'District updated successfully.'
        );
    }
}
