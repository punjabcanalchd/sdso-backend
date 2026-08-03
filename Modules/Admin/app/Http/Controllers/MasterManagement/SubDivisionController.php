<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\SubDivisionService;

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
}
