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

    public function getUsers(int $limit)
    {
        $users = $this->repository->getAll($limit);
        
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
        $idProofTypeMap = [
            1 => 'PAN Card',
            2 => 'Driving License',
        ];

        $idProofType = $user->id_proof;
        if (is_numeric($idProofType) && isset($idProofTypeMap[$idProofType])) {
            $idProofType = $idProofTypeMap[$idProofType];
        }

        $idProofNumber = $user->id_proof_number;
        if (!empty($idProofNumber)) {
            $passPhrase = User::passPhrase();
            $decryptedNumber = User::cryptoJsAesDecrypt($passPhrase, $idProofNumber);
               
            $length = strlen($decryptedNumber);
            if ($length > 4) {
                $idProofNumber = str_repeat('X', $length - 4) . substr($decryptedNumber, -4);
            } else {
                $idProofNumber = $decryptedNumber;
            }
        }

        $fullName = trim($user->applicant_first_name . ' ' . $user->applicant_middle_name . ' ' . $user->applicant_last_name);
        if (empty($fullName)) {
            $fullName = $user->name;
        }

        return [
            'public_id' => $user->public_id,
            'name' => $fullName,
            'role' => $user->mainRole ? $user->mainRole->name : null,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'proof_type' => $idProofType,
            'proof_number' => $idProofNumber,
            'applicant_type' => $user->applicant_type,
            'first_name' => $user->applicant_first_name,
            'middle_name' => $user->applicant_middle_name,
            'last_name' => $user->applicant_last_name,
            'mobileNumber' => $user->mobile_number,
            'designation' => $user->designation,
            'status' => $user->status,
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

        // Set office_district from district_code (already validated in StoreUserRequest)
        if (!empty($data['district_code'])) {
            $data['office_district'] = $data['district_code'];
        }

        // Strip keys that don't belong on the users table before passing to create()
        $additionalRoleIds = $data['additional_role_ids'] ?? [];
        unset($data['additional_role_ids'], $data['district_code']);

            // Create user with main role ID set directly
            $user = $this->repository->create($data);

            // Handle optional additional roles array (write to additional_roles table)
            if (!empty($additionalRoleIds)) {
                $roles = Role::whereIn('id', $additionalRoleIds)->get();

                foreach ($roles as $role) {
                    AdditionalRole::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'deleted' => 0,
                    ]);
                }
            }

            // Sync Spatie model_has_roles from source of truth (role_id + non-deleted additional_roles)
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

            // Update password if present
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Set office_district from district_code (already validated in UpdateUserRequest)
            if (!empty($data['district_code'])) {
                $data['office_district'] = $data['district_code'];
            }

            // Strip keys that don't belong on the users table before passing to update()
            $incomingAdditionalRoleIds = array_key_exists('additional_role_ids', $data) ? $data['additional_role_ids'] : false;
            unset($data['additional_role_ids'], $data['district_code']);

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
                            ['deleted' => 0]
                        );
                    }
                }

                // Soft-delete any additional_roles not in the new list
                AdditionalRole::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->whereNotIn('role_id', $additionalRoleIds)
                    ->update(['deleted' => 1]);
            }

            // Rebuild Spatie model_has_roles from source of truth:
            // users.role_id  +  additional_roles where deleted = 0
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

    public function deleteUser(string $publicId): bool {

        return $this->repository->delete($publicId);
    }

    /* ------------------------------------------------------------------
     * PRIVATE HELPERS
     * ---------------------------------------------------------------- */

    /**
     * Rebuild Spatie's model_has_roles for a user from the two sources of truth:
     *   1. users.role_id          → main role
     *   2. additional_roles table → non-deleted additional roles (deleted = 0)
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

        // 2. All non-deleted additional roles (bypass global scope to be explicit)
        $additionalRoleIds = AdditionalRole::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('deleted', 0)
            ->pluck('role_id')
            ->toArray();

        $roleIds = array_unique(array_merge($roleIds, $additionalRoleIds));

        // Atomically replace all Spatie role assignments for this user
        $user->syncRoles($roleIds);
    }
}