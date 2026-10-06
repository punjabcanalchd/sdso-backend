<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('slider_images', function (Blueprint $table) {
    $table->string('file_type', 255)->default('image')->after('image_name');
        });

        DB::table('slider_images')
    ->whereRaw("LOWER(image_name) LIKE '%.gif'")
    ->update(['file_type' => 'gif']);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('slider_images', function (Blueprint $table) {
    $table->dropColumn('file_type');
});

    }
};
