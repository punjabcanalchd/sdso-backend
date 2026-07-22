<?php

namespace Modules\Admin\Services;

use Spatie\Permission\Models\Permission;
use App\Models\Role;
use App\Models\AdminPage;
use Illuminate\Support\Facades\Crypt;
use Modules\Admin\Services\AdminPagePermissionService;

class ModulePermissionService
{
    /**
     * Decrypt an incoming role public_id and return the Spatie Role model.
     */
    private function resolveRoleByPublicId(string $publicId): Role
    {
        try {
            $id = Crypt::decryptString(urldecode($publicId));
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            abort(404, 'Role not found.');
        }

        return Role::findOrFail($id);
    }

    /**
     * Group permissions by module.
     *
     * @param \Illuminate\Database\Eloquent\Collection|array|null $rolePermissions
     * @return array
     */
    public function getGroupedPermissions($rolePermissions = null)
    {
        $permissions = Permission::all();
        $grouped = [];

        $rolePermissionNames = $rolePermissions ? $rolePermissions->pluck('name')->toArray() : [];

        $actionLabels = [
            'create' => 'Create',
            'read' => 'View',
            'update' => 'Update',
            'destroy' => 'Delete',
        ];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = count($parts) > 1 ? $parts[0] : 'general';
            $action = count($parts) > 1 ? $parts[1] : $permission->name;

            $label = $actionLabels[$action] ?? ucfirst($action);

            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'module' => $module,
                    'label' => ucfirst($module),
                    'permissions' => []
                ];
            }

            $permData = [
                'name'  => $permission->name,
                'label' => $label,
            ];

            if ($rolePermissions !== null) {
                $permData['assigned'] = in_array($permission->name, $rolePermissionNames);
            }

            $grouped[$module]['permissions'][] = $permData;
        }

        return array_values($grouped);
    }

    /**
     * Pre-load all permissions from the DB and group them by the slug prefix
     * (the part before the first dot). This avoids N+1 queries when building
     * the permission tree.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection>
     */
    private function allPermissionsGroupedBySlug(): \Illuminate\Support\Collection
    {
        return Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
    }

    private function getActionLabel($action)
    {
        $labels = [
            'create' => 'Create',
            'read' => 'Read',
            'update' => 'Update',
            'delete' => 'Delete',
        ];
        return $labels[$action] ?? ucfirst($action);
    }

    public function getTree()
    {
        // Single DB query — group all permissions by slug prefix.
        $allPermissions = $this->allPermissionsGroupedBySlug();

        $tree = AdminPage::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return $tree->map(function ($group) use ($allPermissions) {
            $children = $group->children->map(function ($child) use ($allPermissions) {
                $slugPermissions = $allPermissions->get($child->slug, collect());

                $permissions = $slugPermissions->map(function ($permission) use ($child) {
                    $action = substr($permission->name, strlen($child->slug) + 1);
                    return [
                        'action'  => $action,
                        'slug'    => $permission->name,
                        'label'   => $this->getActionLabel($action),
                        'checked' => false,
                    ];
                })->values()->toArray();

                return [
                    'public_id'   => $child->public_id,
                    'name'        => $child->name,
                    'permissions' => $permissions,
                ];
            });

            return [
                'public_id' => $group->public_id,
                'name'      => $group->name,
                'icon'      => $group->icon,
                'children'  => $children,
            ];
        });
    }

    public function getRoleTree(string $publicRoleId)
    {
        $role = $this->resolveRoleByPublicId($publicRoleId);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Single DB query — group all permissions by slug prefix.
        $allPermissions = $this->allPermissionsGroupedBySlug();

        $tree = AdminPage::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return $tree->map(function ($group) use ($allPermissions, $rolePermissions) {
            $children = $group->children->map(function ($child) use ($allPermissions, $rolePermissions) {
                $slugPermissions = $allPermissions->get($child->slug, collect());

                $permissions = $slugPermissions->map(function ($permission) use ($child, $rolePermissions) {
                    $action = substr($permission->name, strlen($child->slug) + 1);
                    return [
                        'action'  => $action,
                        'slug'    => $permission->name,
                        'label'   => $this->getActionLabel($action),
                        'checked' => in_array($permission->name, $rolePermissions),
                    ];
                })->values()->toArray();

                return [
                    'public_id'   => $child->public_id,
                    'name'        => $child->name,
                    'permissions' => $permissions,
                ];
            });

            return [
                'public_id' => $group->public_id,
                'name'      => $group->name,
                'icon'      => $group->icon,
                'children'  => $children,
            ];
        });
    }

    public function syncRolePermissions(string $publicRoleId, array $permissions)
    {
        $role = $this->resolveRoleByPublicId($publicRoleId);
        $role->syncPermissions($permissions);

        // Invalidate permission map cache for all users holding this role when role permissions change.
        AdminPagePermissionService::clearCacheForRole($role);
    }
}
