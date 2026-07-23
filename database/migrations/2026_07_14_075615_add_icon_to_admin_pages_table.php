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
            'Home Page Management' => 'house-door',
            'User Management' => 'people',
            'Log Management' => 'activity',
            'Master Tables' => 'database',
            'Settings' => 'gear',
            'Others' => 'layers',
            'Mobile Menus' => 'phone',
            'Applications' => 'card-list', // Add just in case it exists, though not in the list explicitly.
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
            'Home Page Management',
            'User Management',
            'Log Management',
            'Master Tables',
            'Settings',
            'Others',
            'Mobile Menus',
            'Applications',
        ];

        \App\Models\AdminPage::whereIn('name', $names)->update(['icon' => null]);
    }
};
