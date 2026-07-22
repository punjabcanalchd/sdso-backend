<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\AdminPage;
use Modules\Admin\Services\AdminPagePermissionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->hasRole('Super Admin')) {
                return true;
            }
        });

        Gate::define('view-admin-page', function (User $user, AdminPage $page) {
            return app(AdminPagePermissionService::class)->userCanViewPage($user, $page);
        });
    }
}
