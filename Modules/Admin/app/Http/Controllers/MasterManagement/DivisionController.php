<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\DivisionService;

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
}
