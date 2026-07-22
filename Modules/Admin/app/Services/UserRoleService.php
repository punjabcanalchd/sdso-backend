<?php

namespace Modules\Admin\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\AdditionalRole;
use Modules\Admin\Services\AdminPagePermissionService;

class UserRoleService
{
    /**
     * Assign a role (by name) to a user (resolved via public_id).
     */
    public function assignRoleToUser(string $publicId, string $roleName)
    {
        $user = User::findByPublicId($publicId);
        abort_if(!$user, 404, 'User not found.');

        // Find the role by name using the same guard as the user (api)
        $role = Role::where('name', $roleName)
            ->where('guard_name', 'api')
            ->firstOrFail();

        // Layer 2: Super Admin Check
        if ($role->name === 'Super Admin') {
            $currentUser = auth()->user();
            if (!$currentUser || !$currentUser->hasRole('Super Admin')) {
                abort(403, 'Only a Super Admin can assign the Super Admin role.');
            }
        }

        // Layer 1: Category Check
        if ($user->mainRole && $user->mainRole->role_category_id !== $role->role_category_id) {
            abort(422, 'Cannot assign a role from a different category than the user\'s current role.');
        }

        // Keep the legacy role_id column in sync
        $user->role_id = $role->id;
        $user->save();

        // Rebuild model_has_roles from source of truth:
        // users.role_id  +  additional_roles where deleted = 0
        // This preserves any existing additional roles in Spatie when main role changes.
        $roleIds = [$role->id];
        $additionalRoleIds = AdditionalRole::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('deleted', 0)
            ->pluck('role_id')
            ->toArray();
        $user->syncRoles(array_unique(array_merge($roleIds, $additionalRoleIds)));

        // Invalidate permission map cache when the user's role is updated.
        AdminPagePermissionService::clearCacheForUser($user->id);

        return [
            'public_id'   => $user->public_id,
            'name'        => $user->name,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }

    /**
     * Get a user's roles and permissions (resolved via public_id).
     */
    public function getUserRoleAndPermissions(string $publicId)
    {
        $user = User::findByPublicId($publicId);
        abort_if(!$user, 404, 'User not found.');

        return [
            'public_id'   => $user->public_id,
            'name'        => $user->name,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
