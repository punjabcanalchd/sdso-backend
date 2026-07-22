<?php

namespace Modules\Admin\Repositories\UserManagement;

use App\Models\User;

class UserRepository
{
    /* ------------------------------------------------------------------
     * GET ALL USERS
     * ---------------------------------------------------------------- */

    public function getAll(int $limit)
    {
        return User::latest()->paginate($limit);
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