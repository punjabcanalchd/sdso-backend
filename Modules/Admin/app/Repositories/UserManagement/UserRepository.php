<?php

namespace Modules\Admin\Repositories\UserManagement;

use App\Models\User;

class UserRepository
{
    /* ------------------------------------------------------------------
     * GET ALL USERS
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = User::query();

        if (!empty($search)) {
            $query->where('name', 'ilike', "%{$search}%");
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