<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing roles
        $existingRoles = DB::table('user_roles')->get();

        foreach ($existingRoles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role->role_id],
                [
                    'name' => $role->name ?? 'Role ' . $role->role_id,
                    'guard_name' => 'api',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Map users to roles in Spatie's pivot table
        // Spatie expects 'role_id' (or the pivot key), 'model_type', and 'model_id'
        $users = DB::table('users')->whereNotNull('role_id')->get();
        
        foreach ($users as $user) {
            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $user->role_id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove mapped users and roles
        DB::table('model_has_roles')->where('model_type', 'App\Models\User')->delete();
        DB::table('roles')->where('guard_name', 'api')->delete();
    }
};
