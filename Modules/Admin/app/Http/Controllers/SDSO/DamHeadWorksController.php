<?php

namespace Modules\Admin\Http\Controllers\SDSO;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\SDSO\DamHeadWorksService;
use Modules\Admin\Requests\SDSO\StoreDamHeadWorksRequest;
use Modules\Admin\Requests\SDSO\UpdateDamHeadWorksRequest;

class DamHeadWorksController extends Controller
{
    use ApiResponse;

    protected DamHeadWorksService $service;

    public function __construct(DamHeadWorksService $service)
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
     * Get all without pagination
    */

    public function getAll(Request $request)
    {
       
        $data = $this->service->getAll();

        return $this->successResponse(
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

     /* ------------------------------------------------------------------
     * CREATE
     * ---------------------------------------------------------------- */

    public function store(StoreDamHeadWorksRequest $request)
    {

        $data = $this->service->create($request->validated());

        return $this->successResponse(
            $data,
            'Data created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE
     * ---------------------------------------------------------------- */

    public function update(UpdateDamHeadWorksRequest $request, string $publicId) {

        $data = $this->service->update($request->validated(), $publicId);

        return $this->successResponse(
            $data,
            'Data updated successfully.'
        );
    }
}
