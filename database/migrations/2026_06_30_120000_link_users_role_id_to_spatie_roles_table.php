<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $prefix = DB::getTablePrefix();
        $usersTable = $prefix . 'users';
        $rolesTable = $prefix . 'roles';
        $userRolesTable = $prefix . 'user_roles';

        // Drop the old foreign key (if it exists)
        Schema::table('users', function (Blueprint $table) {
            if (collect(Schema::getForeignKeys('users'))->pluck('columns')->flatten()->contains('role_id')) {
                $table->dropForeign(['role_id']);
            }
        });

        // Sync legacy role_id values to the new Spatie role IDs
        // PostgreSQL UPDATE...FROM: target table alias cannot be used inside FROM JOINs;
        // use a derived subquery instead.
        DB::statement(
            "UPDATE {$usersTable} u "
            ."SET role_id = sub.new_role_id "
            ."FROM ("
            ."    SELECT ur.role_id AS old_role_id, r.id AS new_role_id "
            ."    FROM {$userRolesTable} ur "
            ."    JOIN {$rolesTable} r ON r.name = ur.name"
            .") sub "
            ."WHERE u.role_id = sub.old_role_id"
        );

        // Add the new foreign key that references the Spatie roles table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        $prefix = DB::getTablePrefix();
        $usersTable = $prefix . 'users';
        $rolesTable = $prefix . 'roles';
        $userRolesTable = $prefix . 'user_roles';

        // Drop the foreign key that points to roles
        Schema::table('users', function (Blueprint $table) {
            if (collect(Schema::getForeignKeys('users'))->pluck('columns')->flatten()->contains('role_id')) {
                $table->dropForeign(['role_id']);
            }
        });

        // Reverse the data migration (restore legacy IDs)
        DB::statement(
            "UPDATE {$usersTable} u "
            ."SET role_id = sub.old_role_id "
            ."FROM ("
            ."    SELECT ur.role_id AS old_role_id, r.id AS new_role_id "
            ."    FROM {$userRolesTable} ur "
            ."    JOIN {$rolesTable} r ON r.name = ur.name"
            .") sub "
            ."WHERE u.role_id = sub.new_role_id"
        );

        // Re‑create the original foreign key that referenced the legacy table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('id')
                  ->on('user_roles')
                  ->onDelete('set null');
        });
    }
};