<?php

namespace Modules\Admin\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Admin\Services\UserManagement\UserService;
use Modules\Admin\Requests\UserManagement\StoreUserRequest;
use Modules\Admin\Requests\UserManagement\UpdateUserRequest;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;
    protected UserService $service;

    public function __construct( UserService $service ) {
        $this->service = $service;
    }

    /* ------------------------------------------------------------------
     * GET ALL USERS
     * ---------------------------------------------------------------- */

    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit');

        $maxLimit = config('pagination.max_limit');

        $limit = (int) $request->get('limit',$defaultLimit);

        $limit = min($limit, $maxLimit);

        $limit = max($limit, 1);

        $users = $this->service->getUsers($limit);

        return $this->paginatedResponse(
            $users,
            'Users fetched successfully.'
        );
    }

    /* ------------------------------------------------------------------
     * GET SINGLE USER
     * ---------------------------------------------------------------- */

    public function show(string $id) {

        $user = $this->service->getUser($id);

        return $this->successResponse(
            $user,
            'User fetched successfully.'
        );
    }


    /* ------------------------------------------------------------------
     * CREATE USER
     * ---------------------------------------------------------------- */

    public function store(StoreUserRequest $request) {

        $user = $this->service->createUser($request->validated());

        return $this->successResponse(
            $user,
            'User created successfully.',
            201
        );
    }

    /* ------------------------------------------------------------------
     * UPDATE USER
     * ---------------------------------------------------------------- */

    public function update(UpdateUserRequest $request, string $id) {

        $user = $this->service->updateUser(
            $id,
            $request->validated()
        );

        return $this->successResponse(
            $user,
            'User updated successfully.'
        );
    }


    /* ------------------------------------------------------------------
     * DELETE USER
     * ---------------------------------------------------------------- */

    public function destroy(string $id) {

        $this->service->deleteUser($id);

        return $this->successResponse(
            null,
            'User deleted successfully.'
        );
    }
}