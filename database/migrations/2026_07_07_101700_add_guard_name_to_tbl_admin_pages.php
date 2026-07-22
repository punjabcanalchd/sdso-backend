<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_pages', function (Blueprint $table) {
            // Add guard_name column with default value matching the model default
            $table->string('guard_name')->default('api')->after('status');
        });

        // Back‑fill any existing rows just in case the default was not applied
        DB::table('admin_pages')->update(['guard_name' => 'api']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_pages', function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });
    }
};
