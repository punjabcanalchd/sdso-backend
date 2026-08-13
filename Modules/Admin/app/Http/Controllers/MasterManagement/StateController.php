<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\StateService;
use Modules\Admin\Requests\MasterManagement\StoreStateRequest;
use Modules\Admin\Requests\MasterManagement\UpdateStateRequest;

class StateController extends Controller
{
    use ApiResponse;

    protected StateService $service;

    public function __construct(StateService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all states
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

        $states = $this->service->getStates($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $states,
            'States fetched successfully.'
        );
    }

    /**
     * Get state by slug
     */
    public function show(string $publicId)
    {
        $state = $this->service->getState($publicId);

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

     /* ------------------------------------------------------------------
     * CREATE State
     * ---------------------------------------------------------------- */

    public function store(StoreStateRequest $request)
    {

        $state = $this->service->createState($request->validated());

        return $this->successResponse(
            $state,
            'State created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE State
     * ---------------------------------------------------------------- */

    public function update(UpdateStateRequest $request, string $publicId) {

        $state = $this->service->updateState($request->validated(), $publicId);

        return $this->successResponse(
            $state,
            'State updated successfully.'
        );
    }
}
