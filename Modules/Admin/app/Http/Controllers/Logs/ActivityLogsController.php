<?php

namespace Modules\Admin\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Logs\ActivityLogsService;

class ActivityLogsController extends Controller
{
    use ApiResponse;

    protected ActivityLogsService $service;

    public function __construct(ActivityLogsService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all with pagination
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

        $data = $this->service->get($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $data,
            'Data fetched successfully.'
        );
    }

    /**
     * Get by id
     */
    public function show(string $publicId)
    {
        $data = $this->service->getById($publicId);

        if (! $data) {
            return $this->errorResponse(
                'Data not found.',
                404
            );
        }

        return $this->successResponse(
            $data,
            'Data fetched successfully.'
        );
    }

}
