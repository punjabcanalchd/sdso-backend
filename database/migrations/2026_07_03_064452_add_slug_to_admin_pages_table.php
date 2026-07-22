<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_pages', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Backfill data
        $pages = DB::table('admin_pages')->get();
        foreach ($pages as $page) {
            $slug = Str::slug($page->name);
            
            // Handle duplicates
            $exists = DB::table('admin_pages')->where('slug', $slug)->where('id', '!=', $page->id)->exists();
            if ($exists) {
                $slug .= '-' . $page->id;
            }

            DB::table('admin_pages')
                ->where('id', $page->id)
                ->update(['slug' => $slug]);
        }

        // Now make it unique
        Schema::table('admin_pages', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_pages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
