<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminPage;
use Spatie\Permission\Models\Permission;

class SyncPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync {--dry-run : Show what would be created without writing to DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from existing admin_pages to the permissions table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $guardName = 'api'; // Parameterize guard

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes will be written to the database.');
        }

        $actions = ['create', 'read', 'update', 'delete'];

        // Process leaf pages (skip group placeholders where link = '#')
        // Safely handle NULL links using a logical group.
        $leafPages = AdminPage::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where(function ($query) {
                $query->whereNull('link')
                      ->orWhere('link', '!=', '#');
            })
            ->whereDoesntHave('children')
            ->get();

        // Fetch existing permissions to avoid N+1 queries
        $existingPermissions = Permission::where('guard_name', $guardName)
            ->pluck('name')
            ->flip()
            ->toArray();

        $created = 0;
        $skipped = 0;

        foreach ($leafPages as $page) {
            foreach ($actions as $action) {
                $permissionName = "{$page->slug}.{$action}";

                if (isset($existingPermissions[$permissionName])) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    Permission::create([
                        'name'       => $permissionName,
                        'guard_name' => $guardName,
                    ]);
                }
                $this->line("  [+] {$permissionName}");
                $created++;
            }
        }

        // Invalidate Spatie cache so new permissions are recognized immediately
        if (!$dryRun && $created > 0) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            $this->info("Spatie permission cache cleared.");
        }

        $this->info("Done. Permissions created: {$created}, already existed: {$skipped}.");

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes were written to the database.');
        }
    }
}
