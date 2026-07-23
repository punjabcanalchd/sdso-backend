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
        // Cache Table
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        } else {
            Schema::table('cache', function (Blueprint $table) {
                if (!Schema::hasColumn('cache', 'key')) {
                    $table->string('key')->primary();
                }

                if (!Schema::hasColumn('cache', 'value')) {
                    $table->mediumText('value');
                }

                if (!Schema::hasColumn('cache', 'expiration')) {
                    $table->bigInteger('expiration')->index();
                }
            });
        }

        // Cache Locks Table
        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        } else {
            Schema::table('cache_locks', function (Blueprint $table) {
                if (!Schema::hasColumn('cache_locks', 'key')) {
                    $table->string('key')->primary();
                }

                if (!Schema::hasColumn('cache_locks', 'owner')) {
                    $table->string('owner');
                }

                if (!Schema::hasColumn('cache_locks', 'expiration')) {
                    $table->bigInteger('expiration')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};