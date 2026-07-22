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
        Schema::create('gw_application_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('application_id');
            $table->unsignedSmallInteger('service_type');

            $table->timestamps();

            $table->index('application_id');
            $table->index('service_type');

            $table->foreign('application_id')
                ->references('application_id')
                ->on('gw_applications')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gw_application_services');
    }
};
