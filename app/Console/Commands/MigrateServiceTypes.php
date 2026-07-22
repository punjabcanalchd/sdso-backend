<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:migrate-service-types')]
#[Description('Migrate serialized service types into tbl_gw_application_services')]
class MigrateServiceTypes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting service type migration...');

        $migrated = 0;
        $skipped = 0;

        DB::table('gw_applications')
            ->select('application_id', 'service_type')
            ->orderBy('application_id')
            ->chunk(500, function ($applications) use (&$migrated, &$skipped) {

                $rows = [];

                foreach ($applications as $application) {

                    if (empty($application->service_type)) {
                        continue;
                    }

                    $value = trim($application->service_type);

                    // Handle serialized arrays and plain numeric values
                    if (str_starts_with($value, 'a:')) {
                        $services = @unserialize($value);

                        if (!is_array($services)) {
                            $this->warn(
                                "Invalid serialized data for application ID {$application->application_id}"
                            );
                            $skipped++;
                            continue;
                        }
                    } elseif (is_numeric($value)) {
                        $services = [(int) $value];
                    } else {
                        $this->warn(
                            "Invalid service_type '{$value}' for application ID {$application->application_id}"
                        );
                        $skipped++;
                        continue;
                    }

                    foreach ($services as $serviceType) {

                        $rows[] = [
                            'application_id' => $application->application_id,
                            'service_type'   => (int) $serviceType,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        $migrated++;
                    }
                }

                if (!empty($rows)) {
                    DB::table('gw_application_services')
                        ->upsert(
                            $rows,
                            ['application_id', 'service_type']
                        );
                }
            });

        $this->newLine();
        $this->info('Service type migration completed successfully.');
        $this->info("Migrated records: {$migrated}");
        $this->info("Skipped records: {$skipped}");

        return self::SUCCESS;
    }
}