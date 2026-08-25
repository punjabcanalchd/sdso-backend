<?php

namespace Modules\Admin\Http\Controllers\MasterManagement;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\MasterManagement\CircleService;
use Modules\Admin\Requests\MasterManagement\StoreCircleRequest;
use Modules\Admin\Requests\MasterManagement\UpdateCircleRequest;

class CircleController extends Controller
{
    use ApiResponse;

    protected CircleService $service;

    public function __construct(CircleService $service)
    {
        $this->service = $service;
    }


    /**
     * Get all Circles
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

        $circles = $this->service->getCircles($limit, $search, $sort_column, $sort_direction);

        return $this->paginatedResponse(
            $circles,
            'Circles fetched successfully.'
        );
    }

    public function getAllCircles(Request $request)
    {
       
        $circles = $this->service->getAllCircles();

        return $this->successResponse(
            $circles,
            'Circles fetched successfully.'
        );
    }

    /**
     * Get Circle by id
     */
    public function show(string $publicId)
    {
        $circle = $this->service->getCircle($publicId);

        if (! $circle) {
            return $this->errorResponse(
                'Circle not found.',
                404
            );
        }

        return $this->successResponse(
            $circle,
            'Circle fetched successfully.'
        );
    }

     /* ------------------------------------------------------------------
     * CREATE Circle
     * ---------------------------------------------------------------- */

    public function store(StoreCircleRequest $request)
    {

        $circle = $this->service->createCircle($request->validated());

        return $this->successResponse(
            $circle,
            'Circle created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE Circle
     * ---------------------------------------------------------------- */

    public function update(UpdateCircleRequest $request, string $publicId) {

        $circle = $this->service->updateCircle($request->validated(), $publicId);

        return $this->successResponse(
            $circle,
            'Circle updated successfully.'
        );
    }
}
