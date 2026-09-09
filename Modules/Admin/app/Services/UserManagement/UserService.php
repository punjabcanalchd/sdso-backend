<?php

namespace Modules\Admin\Services\UserManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Repositories\UserManagement\UserRepository;
use App\Models\Role;
use App\Models\AdditionalRole;
use App\Models\User;
use Modules\Admin\Services\AdminPagePermissionService;

class UserService
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository) {
        $this->repository = $repository;
    }

    /* ------------------------------------------------------------------
     * GET ALL USERS
     * ---------------------------------------------------------------- */

    public function getUsers(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction, array $filters = [])
    {
        $users = $this->repository->getAll($limit, $search, $sort_column, $sort_direction, $filters);
        
        $users->getCollection()->transform(function ($user) {
            return $this->formatUser($user);
        });

        return $users;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE USER
     * ---------------------------------------------------------------- */

    public function getUser(string $publicId) {
        $user = $this->repository->findByPublicId($publicId);
        return $this->formatUser($user);
    }

    private function formatUser($user)
    {

        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'hrmscode' => $user->hrmscode,
            'role' => $user->mainRole ? $user->mainRole->name : null,
            'selected_roles_names' => $user->roles ? $user->roles->pluck('name')->toArray() : [],
            'email' => $user->email,
            'created_at' => $user->created_at,
            'office'          => $user->office->officename ?? null,
            'officecode'      => $user->officecode ? \App\Models\Office::find($user->officecode)?->public_id : null,
            'officelevelcode' => $user->officelevelcode ? \App\Models\OfficeHierarchies::find($user->officelevelcode)?->public_id : null,
            'district_code'   => $user->officecode ? \App\Models\Office::find($user->officecode)?->lgddistcode : null,
            'circle_id'       => $user->circle_id ? \App\Models\Circles::find($user->circle_id)?->public_id : null,
            'division_id'     => $user->division_id ? \App\Models\Divisions::find($user->division_id)?->public_id : null,
            'subdivision_id'  => $user->subdivision_id ? \App\Models\SubDivisions::find($user->subdivision_id)?->public_id : null,
            'retirementdate'  => $user->retirementdate,
            'mobileNumber' => $user->mobile_number,
            'designation' => $user->designation,
            'status' => $user->status,
            'locked' => (bool) $user->locked,
        ];
    }

    /* ------------------------------------------------------------------
     * CREATE USER
     * ---------------------------------------------------------------- */

    public function createUser(array $data)
    {
        DB::beginTransaction();

        try {
            // Map name fields
            if (isset($data['first_name'])) {
                $data['name'] = $data['first_name'];
                $data['applicant_first_name'] = $data['first_name'];
            }
            if (array_key_exists('middle_name', $data)) {
                $data['applicant_middle_name'] = $data['middle_name'];
            }
            if (isset($data['last_name'])) {
                $data['applicant_last_name'] = $data['last_name'];
            }
            unset($data['first_name'], $data['middle_name'], $data['last_name']);

            // Hash passwords
            $data['password'] = Hash::make($data['password']);
            if (!empty($data['mobile_password'])) {
                $data['mobile_password'] = Hash::make($data['mobile_password']);
            }
            $data['password_updated_at'] = now();

        // Strip keys that don't belong on the users table before passing to create()
        $additionalRoleIds = $data['additional_role_ids'] ?? [];
        unset($data['additional_role_ids'], $data['district_code']);
            // Resolve officecode from public_id
            if (!empty($data['officecode'])) {
                $office = \App\Models\Office::findByPublicId($data['officecode']);
                $data['officecode'] = $office ? $office->officecode : null;
            }
            if (!empty($data['officelevelcode'])) {
                $officeHierarchy = \App\Models\OfficeHierarchies::findByPublicId($data['officelevelcode']);
                $data['officelevelcode'] = $officeHierarchy ? $officeHierarchy->officelevelcode : null;
            }
            
            // Decode dropdown public_ids to integers using their respective models
            if (!empty($data['circle_id'])) {
                $circle = \App\Models\Circles::findByPublicId($data['circle_id']);
                $data['circle_id'] = $circle ? $circle->circle_id : null;
            }
            if (!empty($data['division_id'])) {
                $division = \App\Models\Divisions::findByPublicId($data['division_id']);
                $data['division_id'] = $division ? $division->division_id : null;
            }
            if (!empty($data['subdivision_id'])) {
                $subdivision = \App\Models\SubDivisions::findByPublicId($data['subdivision_id']);
                $data['subdivision_id'] = $subdivision ? $subdivision->subdivision_id : null;
            }
            // Check if a locked user exists with the same email
            $existingLockedUser = \App\Models\User::where('email', $data['email'])
                ->where('locked', true)
                ->first();

            if ($existingLockedUser) {
                $data['locked'] = false;
                $data['status'] = true; // ensure it is active
                $user = $this->repository->update($existingLockedUser->public_id, $data);
                
                // Clear out old additional roles as we are recycling the user
                \App\Models\AdditionalRole::where('user_id', $user->id)->delete();
            } else {
                $data['locked'] = false;
                $user = $this->repository->create($data);
            }
            // Handle optional additional roles array (write to additional_roles table)
            if (!empty($additionalRoleIds)) {
                $roles = Role::whereIn('id', $additionalRoleIds)->get();

                foreach ($roles as $role) {
                    AdditionalRole::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'status' => 1,
                        'officecode' => $data['officecode'] ?? null, 
                    ]);
                }
            }

            // Sync Spatie model_has_roles from source of truth (role_id + active additional_roles)
            $this->resyncSpatieRoles($user);

            // Invalidate permission map cache when the user's roles are assigned.
            AdminPagePermissionService::clearCacheForUser($user->id);

            DB::commit();
            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE USER
     * ---------------------------------------------------------------- */

    public function updateUser(string $publicId, array $data)
    {
        $userForValidation = $this->repository->findByPublicId($publicId);
        
        if (isset($data['role_id'])) {
            $newMainRole = Role::findOrFail($data['role_id']);
            
            // Layer 2: Super Admin Check
            if ($newMainRole->name === 'Super Admin') {
                $currentUser = auth()->user();
                if (!$currentUser || !$currentUser->hasRole('Super Admin')) {
                    abort(403, 'Only a Super Admin can assign the Super Admin role.');
                }
            }

            // Layer 1: Category Check
            if ($userForValidation->mainRole && $userForValidation->mainRole->role_category_id !== $newMainRole->role_category_id) {
                abort(422, 'Cannot assign a role from a different category than the user\'s current role.');
            }
        }

        DB::beginTransaction();
        try {
            // Map name fields
            if (isset($data['first_name'])) {
                $data['name'] = $data['first_name'];
                $data['applicant_first_name'] = $data['first_name'];
            }
            if (array_key_exists('middle_name', $data)) {
                $data['applicant_middle_name'] = $data['middle_name'];
            }
            if (isset($data['last_name'])) {
                $data['applicant_last_name'] = $data['last_name'];
            }
            unset($data['first_name'], $data['middle_name'], $data['last_name']);

            // Update password if present and not empty
            if (!empty($data['password'])) {
                $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            } else {
                unset($data['password']);
            }


            // Strip keys that don't belong on the users table before passing to update()
            $incomingAdditionalRoleIds = array_key_exists('additional_role_ids', $data) ? $data['additional_role_ids'] : false;
            unset($data['additional_role_ids'], $data['district_code']);
            // Resolve officecode from public_id
            if (!empty($data['officecode'])) {
                $office = \App\Models\Office::findByPublicId($data['officecode']);
                $data['officecode'] = $office ? $office->officecode : null;
            }
            if (!empty($data['officelevelcode'])) {
                $officeHierarchy = \App\Models\OfficeHierarchies::findByPublicId($data['officelevelcode']);
                $data['officelevelcode'] = $officeHierarchy ? $officeHierarchy->officelevelcode : null;
            }

            // Decode dropdown public_ids to integers using their respective models
            if (!empty($data['circle_id'])) {
                $circle = \App\Models\Circles::findByPublicId($data['circle_id']);
                $data['circle_id'] = $circle ? $circle->circle_id : null;
            }
            if (!empty($data['division_id'])) {
                $division = \App\Models\Divisions::findByPublicId($data['division_id']);
                $data['division_id'] = $division ? $division->division_id : null;
            }
            if (!empty($data['subdivision_id'])) {
                $subdivision = \App\Models\SubDivisions::findByPublicId($data['subdivision_id']);
                $data['subdivision_id'] = $subdivision ? $subdivision->subdivision_id : null;
            }

            // Update the user record (including role_id column)
            $user = $this->repository->update($publicId, $data);

            // Handle additional role logic – expects an array of role IDs
            if ($incomingAdditionalRoleIds !== false) {
                $additionalRoleIds = $incomingAdditionalRoleIds ?? [];

                if (!empty($additionalRoleIds)) {
                    $roles = Role::whereIn('id', $additionalRoleIds)->get();

                    // Safety check – validation already ensures IDs exist, but double-check
                    if ($roles->count() !== count($additionalRoleIds)) {
                        throw new \Illuminate\Database\Eloquent\ModelNotFoundException('One or more roles not found.');
                    }

                    foreach ($roles as $role) {
                        // Insert or reactivate the additional_roles entry (no duplicates)
                        AdditionalRole::withoutGlobalScopes()->updateOrCreate(
                            ['user_id' => $user->id, 'role_id' => $role->id],
                            ['status' => 1,  'officecode' => $data['officecode'] ?? $user->officecode]
                        );
                    }
                }

                // Soft-delete any additional_roles not in the new list
                AdditionalRole::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->whereNotIn('role_id', $additionalRoleIds)
                    ->update(['status' => 0]);
            }

            // Rebuild Spatie model_has_roles from source of truth:
            // users.role_id  +  additional_roles where status = 1
            $this->resyncSpatieRoles($user);

            // Invalidate permission map cache when the user's roles are updated.
            AdminPagePermissionService::clearCacheForUser($user->id);

            DB::commit();
            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ------------------------------------------------------------------
     * DELETE USER
     * ---------------------------------------------------------------- */

    public function updateStatus(string $publicId, array $data): User{

        $user =  $this->repository->update($publicId, $data);
        return $user;
    }

       public function deleteUser(string $publicId): bool {
        $user = $this->repository->findByPublicId($publicId);
        
        $user->status = false;
        $user->locked = true;
        $user->email = $user->email;
        $user->save();
        
        return true;
    }


    /* ------------------------------------------------------------------
     * PRIVATE HELPERS
     * ---------------------------------------------------------------- */

    /**
     * Rebuild Spatie's model_has_roles for a user from the two sources of truth:
     *   1. users.role_id          → main role
     *   2. additional_roles table → active additional roles (status = 1)
     *
     * Called after any mutation of the user's main role or additional roles
     * so that model_has_roles never goes out of sync.
     */
    private function resyncSpatieRoles(User $user): void
    {
        // Refresh to get latest role_id after repository update
        $user->refresh();

        $roleIds = [];

        // 1. Main role from users.role_id
        if ($user->role_id) {
            $roleIds[] = $user->role_id;
        }

        // 2. All active additional roles (bypass global scope to be explicit)
        $additionalRoleIds = AdditionalRole::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->pluck('role_id')
            ->toArray();

        $roleIds = array_unique(array_merge($roleIds, $additionalRoleIds));

        // Atomically replace all Spatie role assignments for this user
        $user->syncRoles($roleIds);
    }
}