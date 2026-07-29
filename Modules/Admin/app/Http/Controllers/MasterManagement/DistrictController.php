<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\DistrictService;

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

        $states = $this->service->getDistricts($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $states,
            'States fetched successfully.'
        );
    }

    /**
     * Get page by slug
     */
    public function show(string $publicId)
    {
        $state = $this->service->getDistrict($publicId);

        if (! $state) {
            return $this->errorResponse(
                'State not found.',
                404
            );
        }

        return $this->successResponse(
            $state,
            'State fetched successfully.'
        );
    }
}
