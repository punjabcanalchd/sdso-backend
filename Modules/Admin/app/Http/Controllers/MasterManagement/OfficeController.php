<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\OfficeService;

class OfficeController extends Controller
{
    use ApiResponse;

    protected OfficeService $service;

    public function __construct(OfficeService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all Offices
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

        $offices = $this->service->getOffices($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $offices,
            'Offices fetched successfully.'
        );
    }

    /**
     * Get Office by id
     */
    public function show(string $publicId)
    {
        $office = $this->service->getOffice($publicId);

        if (! $office) {
            return $this->errorResponse(
                'Office not found.',
                404
            );
        }

        return $this->successResponse(
            $office,
            'Office fetched successfully.'
        );
    }
}
