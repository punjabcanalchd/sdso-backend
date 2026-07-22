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
        $roles = [

            1 => 'applicant',
            2 => 'admin',
            3 => 'admin',
            4 => 'admin',
            5 => 'officer',
            6 => 'officer',
            7 => 'officer',
            8 => 'officer',
            9 => 'officer',
            10 => 'dso',
            11 => 'accountant',
            12 => 'report',
            13 => 'water-resource-user',
            14 => 'report',
            15 => 'admin',
            16 => 'admin',
            17 => 'dwlr',
            18 => 'dwlr',
            19 => 'dwlr',
            20 => 'dwlr',
            21 => 'dwlr',
            22 => 'dwlr',
            23 => 'dwlr',
            24 => 'dwlr',
            25 => 'account-executive'
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