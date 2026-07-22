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
        Schema::table('gw_application_services', function (Blueprint $table) {
            $table->unique(
                ['application_id', 'service_type'],
                'uq_gw_application_service'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gw_application_services', function (Blueprint $table) {
            $table->dropUnique('uq_gw_application_service');
        });
    }
};
