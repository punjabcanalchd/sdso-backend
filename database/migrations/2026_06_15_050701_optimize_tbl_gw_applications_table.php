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
        DB::statement("
            ALTER TABLE tbl_gw_applications
                ALTER COLUMN application_no TYPE VARCHAR(50),
                ALTER COLUMN unit_id TYPE VARCHAR(50),
                ALTER COLUMN permission_no TYPE VARCHAR(50),

                ALTER COLUMN application_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(application_date), '')::timestamp,

                ALTER COLUMN permission_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(permission_date), '')::timestamp,

                ALTER COLUMN valid_upto TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(valid_upto), '')::timestamp,

                ALTER COLUMN rejection_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(rejection_date), '')::timestamp,

                ALTER COLUMN amd_permission_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(amd_permission_date), '')::timestamp
        ");

        DB::statement("
            ALTER TABLE tbl_gw_application_logs
                ALTER COLUMN application_no TYPE VARCHAR(50),
                ALTER COLUMN unit_id TYPE VARCHAR(50),
                ALTER COLUMN permission_no TYPE VARCHAR(50),

                ALTER COLUMN application_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(application_date), '')::timestamp,

                ALTER COLUMN permission_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(permission_date), '')::timestamp,

                ALTER COLUMN valid_upto TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(valid_upto), '')::timestamp,

                ALTER COLUMN rejection_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(rejection_date), '')::timestamp,

                ALTER COLUMN amd_permission_date TYPE TIMESTAMP WITHOUT TIME ZONE
                USING NULLIF(TRIM(amd_permission_date), '')::timestamp
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE tbl_gw_applications
                ALTER COLUMN application_no TYPE VARCHAR(255),
                ALTER COLUMN unit_id TYPE VARCHAR(255),
                ALTER COLUMN permission_no TYPE VARCHAR(255),

                ALTER COLUMN application_date TYPE VARCHAR(255)
                USING application_date::text,

                ALTER COLUMN permission_date TYPE VARCHAR(255)
                USING permission_date::text,

                ALTER COLUMN valid_upto TYPE VARCHAR(255)
                USING valid_upto::text,

                ALTER COLUMN rejection_date TYPE VARCHAR(255)
                USING rejection_date::text,

                ALTER COLUMN amd_permission_date TYPE VARCHAR(255)
                USING amd_permission_date::text
        ");

        DB::statement("
            ALTER TABLE tbl_gw_application_logs
                ALTER COLUMN application_no TYPE VARCHAR(255),
                ALTER COLUMN unit_id TYPE VARCHAR(255),
                ALTER COLUMN permission_no TYPE VARCHAR(255),

                ALTER COLUMN application_date TYPE VARCHAR(255)
                USING application_date::text,

                ALTER COLUMN permission_date TYPE VARCHAR(255)
                USING permission_date::text,

                ALTER COLUMN valid_upto TYPE VARCHAR(255)
                USING valid_upto::text,

                ALTER COLUMN rejection_date TYPE VARCHAR(255)
                USING rejection_date::text,

                ALTER COLUMN amd_permission_date TYPE VARCHAR(255)
                USING amd_permission_date::text
        ");
    }
};
