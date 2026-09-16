<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdditionalRole;
use App\Models\Circles;
use App\Models\Divisions;
use App\Models\Office;
use App\Models\OfficeHierarchies;
use App\Models\Role;
use App\Models\SubDivisions;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\AdminPagePermissionService;

class AdditionalRoleController extends Controller
{
    use ApiResponse;

    /**
     * Get paginated Additional Roles with dynamic search and filters.
     */
    public function index(Request $request)
    {
        $defaultLimit = config('pagination.default_limit', 10);
        $maxLimit     = config('pagination.max_limit', 100);
        $limit        = min(max((int) $request->input('per_page', $defaultLimit), 1), $maxLimit);

        $query = AdditionalRole::withoutGlobalScopes()
            ->with([
                'user:id,name,email,hrmscode',
                'role:id,name',
                'office.officeDescription'
            ]);

        // 1. Text Search (HRMS Code, Email, User Name, Role Name)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('hrmscode', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%");
                })->orWhereHas('role', function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 2. Status Filter
        if ($request->filled('status')) {
            $query->where('status', (int) $request->input('status'));
        }

        // 3. Hierarchy / Office Filters
        if ($request->filled('officecode')) {
            $office = is_numeric($request->officecode) 
                ? Office::find($request->officecode) 
                : Office::findByPublicId($request->officecode);
            if ($office) {
                $query->where('officecode', $office->officecode);
            }
        } elseif ($request->filled('subdivision_id')) {
            $subdivision = is_numeric($request->subdivision_id)
                ? SubDivisions::find($request->subdivision_id)
                : SubDivisions::findByPublicId($request->subdivision_id);
            if ($subdivision) {
                $query->whereHas('office', fn ($oq) => $oq->where('subdivision_id', $subdivision->subdivision_id));
            }
        } elseif ($request->filled('division_id')) {
            $division = is_numeric($request->division_id)
                ? Divisions::find($request->division_id)
                : Divisions::findByPublicId($request->division_id);
            if ($division) {
                $query->whereHas('office', fn ($oq) => $oq->where('division_id', $division->division_id));
            }
        } elseif ($request->filled('circle_id')) {
            $circle = is_numeric($request->circle_id)
                ? Circles::find($request->circle_id)
                : Circles::findByPublicId($request->circle_id);
            if ($circle) {
                $query->whereHas('office', fn ($oq) => $oq->where('circle_id', $circle->circle_id));
            }
        } elseif ($request->filled('officelevelcode')) {
            $level = is_numeric($request->officelevelcode)
                ? OfficeHierarchies::find($request->officelevelcode)
                : OfficeHierarchies::findByPublicId($request->officelevelcode);
            if ($level) {
                $query->whereHas('office', fn ($oq) => $oq->where('officelevelcode', $level->officelevelcode));
            }
        }


        // 4. Sorting
        $sortColumn    = $request->input('sort_column', 'id');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortColumn === 'hrmscode') {
            $query->join('users', 'additional_roles.user_id', '=', 'users.id')
                  ->orderBy('users.hrmscode', $sortDirection)
                  ->select('additional_roles.*');
        } elseif ($sortColumn === 'email') {
            $query->join('users', 'additional_roles.user_id', '=', 'users.id')
                  ->orderBy('users.email', $sortDirection)
                  ->select('additional_roles.*');
        } elseif ($sortColumn === 'role_name') {
            $query->join('roles', 'additional_roles.role_id', '=', 'roles.id')
                  ->orderBy('roles.name', $sortDirection)
                  ->select('additional_roles.*');
        } else {
            $query->orderBy('additional_roles.id', $sortDirection);
        }

        $roles = $query->paginate($limit);

        return $this->paginatedResponse($roles, 'Additional roles fetched successfully.');
    }

    /**
     * Get single Additional Role details.
     */
    public function show(string $publicId)
    {
        $additionalRole = AdditionalRole::withoutGlobalScopes()
            ->with(['user', 'role', 'office.officeDescription'])
            ->wherePublicId($publicId)
            ->first();

        if (!$additionalRole) {
            return $this->errorResponse('Additional role not found.', 404);
        }

        return $this->successResponse($additionalRole, 'Additional role details retrieved.');
    }

    /**
     * Create Additional Role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required',
            'role_id'    => 'required',
            'officecode' => 'required',
            'status'     => 'required',
        ]);

        $user = is_numeric($request->user_id) 
            ? User::find($request->user_id) 
            : User::findByPublicId($request->user_id);

        $role = is_numeric($request->role_id) 
            ? Role::find($request->role_id) 
            : Role::findByPublicId($request->role_id);

        $office = is_numeric($request->officecode) 
            ? Office::find($request->officecode) 
            : Office::findByPublicId($request->officecode);

        if (!$user || !$role || !$office) {
            return $this->errorResponse('User, Role, or Office not found.', 422);
        }

        $additionalRole = AdditionalRole::withoutGlobalScopes()->create([
            'user_id'    => $user->id,
            'role_id'    => $role->id,
            'officecode' => $office->officecode,
            'status'     => (int) $request->status,
        ]);

        // Sync Spatie model_has_roles
        $this->syncUserRoles($user);

        return $this->successResponse($additionalRole, 'Additional role created successfully.', 201);
    }

    /**
     * Update Additional Role.
     */
    public function update(Request $request, string $publicId)
    {
        $additionalRole = AdditionalRole::withoutGlobalScopes()
            ->wherePublicId($publicId)
            ->first();

        if (!$additionalRole) {
            return $this->errorResponse('Additional role not found.', 404);
        }

        $user = is_numeric($request->user_id) 
            ? User::find($request->user_id) 
            : User::findByPublicId($request->user_id);

        $role = is_numeric($request->role_id) 
            ? Role::find($request->role_id) 
            : Role::findByPublicId($request->role_id);

        $office = is_numeric($request->officecode) 
            ? Office::find($request->officecode) 
            : Office::findByPublicId($request->officecode);

        $additionalRole->update([
            'user_id'    => $user ? $user->id : $additionalRole->user_id,
            'role_id'    => $role ? $role->id : $additionalRole->role_id,
            'officecode' => $office ? $office->officecode : $additionalRole->officecode,
            'status'     => isset($request->status) ? (int) $request->status : $additionalRole->status,
        ]);

        // Sync Spatie model_has_roles
        if ($user) {
            $this->syncUserRoles($user);
        }

        return $this->successResponse($additionalRole, 'Additional role updated successfully.');
    }

    /**
     * Delete / Inactivate Additional Role.
     */
    public function destroy(string $publicId)
    {
        $additionalRole = AdditionalRole::withoutGlobalScopes()
            ->wherePublicId($publicId)
            ->first();

        if (!$additionalRole) {
            return $this->errorResponse('Additional role not found.', 404);
        }

        $user = $additionalRole->user;
        $additionalRole->delete();

        if ($user) {
            $this->syncUserRoles($user);
        }

        return $this->successResponse(null, 'Additional role deleted successfully.');
    }

    /**
     * Syncs Spatie model_has_roles with source of truth (role_id + active additional_roles).
     */
    private function syncUserRoles(User $user): void
    {
        $roleIds = [$user->role_id];
        $additionalRoleIds = AdditionalRole::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->pluck('role_id')
            ->toArray();

        $user->syncRoles(array_unique(array_filter(array_merge($roleIds, $additionalRoleIds))));
        AdminPagePermissionService::clearCacheForUser($user->id);
    }
}
