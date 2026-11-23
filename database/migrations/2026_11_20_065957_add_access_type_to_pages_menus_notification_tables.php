<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_type')->default(1)->comment('1 = Public, 2 = Admin');
        });
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_type')->default(1)->comment('1 = Public, 2 = Admin');
        });
        Schema::table('noticeboard_templates', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_type')->default(1)->comment('1 = Public, 2 = Admin');
        });
        Schema::table('category_templates', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_type')->default(1)->comment('1 = Public, 2 = Admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
        Schema::table('noticeboard_templates', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
        Schema::table('category_templates', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
    }
};
