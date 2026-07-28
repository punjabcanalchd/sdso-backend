<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\Crypt;
use App\Models\Role;
use \App\Models\RoleCategory;

class RoleService
{
    /**
     * Encrypt a Spatie Role's integer ID into a URL-safe public_id.
     * Used since Spatie's Role model does not use HasPublicId.
     */
    private function encryptRoleId(int $id): string
    {
        return urlencode(Crypt::encryptString((string) $id));
    }

    /**
     * Decrypt an incoming public_id back to the real integer role ID
     * and return the Role model.
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

    public function getAllRoles(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Role::with('category')->where('guard_name', 'api');

        if (!empty($search)) {
            $query->where('name', 'ilike', "%{$search}%");
        }
        
        $sort_column = $sort_column ?: 'id';

        $sort_direction = strtolower($sort_direction ?? 'desc');

        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'desc';
        }
        $roles = $query->orderBy($sort_column, $sort_direction)
                   ->paginate($limit);

        $grouped = $roles->getCollection()
            ->groupBy(fn($role) => $role->category->name ?? 'Uncategorized')
            ->map(function ($categoryRoles, $categoryName) {

                return [
                    'name' => $categoryName,
                    'roles' => $categoryRoles->map(function ($role) {
                        return [
                            'public_id'  => $this->encryptRoleId($role->id),
                            'name'       => $role->name,
                            'guard_name' => $role->guard_name,
                            'created_at' => $role->created_at,
                            'updated_at' => $role->updated_at,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $roles->setCollection($grouped);

        return $roles;
    }

    public function createRole(array $data)
    {
        $defaultCategory = RoleCategory::where('is_default', true)->first();
        
        $role = Role::create([
            'name'             => $data['name'],
            'guard_name'       => 'api',
            'role_category_id' => $defaultCategory ? $defaultCategory->id : null,
        ]);

        return [
            'public_id'  => $this->encryptRoleId($role->id),
            'name'       => $role->name,
            'guard_name' => $role->guard_name,
            'created_at' => $role->created_at,
        ];
    }

    public function updateRole(string $publicId, array $data)
    {
        $role = $this->resolveRoleByPublicId($publicId);
        $role->name = $data['name'];
        $role->save();

        return [
            'public_id'  => $this->encryptRoleId($role->id),
            'name'       => $role->name,
            'guard_name' => $role->guard_name,
            'updated_at' => $role->updated_at,
        ];
    }
}
