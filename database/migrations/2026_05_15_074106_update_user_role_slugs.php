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
        DB::table('user_roles')->insert([
            'role_id' => 1,
            'name' => 'Super Admin',
            'slug'    => null,
            'permissions'    => null,
            'sort_order'    => 1,
        ]);

        $roles = [

            1 => 'super-admin',
            2 => 'admin',
            3 => 'sub_divisional_officer',
            4 => 'hod',
            5 => 'superintending_engineer',
            6 => 'executive-engineer',
            7 => 'junior-engineer',
            8 => 'gauge_reader_beldar',
            9 => 'superintendent',
            10 => 'head_signaler',
            11 => 'department-role',
            12 => 'xen_reguation',
        ];

        foreach ($roles as $id => $slug) {

            DB::table('user_roles')
                ->where('role_id', $id)
                ->update([
                    'slug' => $slug
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_roles')
            ->update([
                'slug' => null
            ]);
    }
};