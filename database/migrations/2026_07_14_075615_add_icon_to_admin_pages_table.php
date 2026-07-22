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
        $icons = [
            'Manage Fees Structure' => 'cash-stack',
            'Workflow Management' => 'diagram-3',
            'Home Page Management' => 'house-door',
            'User Management' => 'people',
            'Application Management' => 'ui-checks-grid',
            'Log Management' => 'activity',
            'Master Tables' => 'database',
            'Settings' => 'gear',
            'Others' => 'layers',
            'Mobile Menus' => 'phone',
            'Applications' => 'card-list', // Add just in case it exists, though not in the list explicitly.
            'Cancel Or Suspension' => 'x-circle',
        ];

        foreach ($icons as $name => $icon) {
            \App\Models\AdminPage::where('name', $name)->update(['icon' => $icon]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $names = [
            'Manage Fees Structure',
            'Workflow Management',
            'Home Page Management',
            'User Management',
            'Application Management',
            'Log Management',
            'Master Tables',
            'Settings',
            'Others',
            'Mobile Menus',
            'Applications',
            'Cancel Or Suspension',
        ];

        \App\Models\AdminPage::whereIn('name', $names)->update(['icon' => null]);
    }
};
