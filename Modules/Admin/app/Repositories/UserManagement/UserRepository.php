<?php

namespace Modules\Admin\Repositories\UserManagement;

use App\Models\User;
use App\Traits\HasPublicId;

class UserRepository
{
    use HasPublicId;
    /* ------------------------------------------------------------------
     * GET ALL USERS
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction, array $filters = [])
    {
        $query = User::query();
        
        // Exclude logically deleted users
        $query->where('locked', false);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('hrmscode', 'ilike', "%{$search}%");
            });
        }

        // Apply filters — public_ids decoded to integer IDs
        if (!empty($filters['circle_id'])) {
            $query->where('circle_id', $this->decode($filters['circle_id']));
        }

        if (!empty($filters['division_id'])) {
            $query->where('division_id', $this->decode($filters['division_id']));
        }

        if (!empty($filters['subdivision_id'])) {
            $query->where('subdivision_id', $this->decode($filters['subdivision_id']));
        }

        if (!empty($filters['officecode'])) {
            $query->where('officecode', $this->decode($filters['officecode']));
        }

        if (!empty($filters['role_id'])) {
            try {
                $role_id = \Illuminate\Support\Facades\Crypt::decryptString(urldecode($filters['role_id']));
                $query->where('role_id', $role_id);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Ignore invalid role id format
            }
        }
        
        $sort_column = $sort_column ?: 'id';

        $sort_direction = strtolower($sort_direction ?? 'desc');

        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'desc';
        }
        $query->orderBy($sort_column, $sort_direction);

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE USER BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): User
    {
        $user = User::findByPublicId($publicId);
        abort_if(!$user, 404, 'User not found.');
        return $user;
    }

    /* ------------------------------------------------------------------
     * CREATE USER
     * ---------------------------------------------------------------- */

    public function create(array $data): User
    {
        return User::create($data);
    }

    /* ------------------------------------------------------------------
     * UPDATE USER
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): User
    {
        $user = $this->findByPublicId($publicId);
        $user->update($data);
        return $user;
    }

    /* ------------------------------------------------------------------
     * DELETE USER
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $user = $this->findByPublicId($publicId);
        return $user->delete();
    }
}