<?php

namespace Database\Seeders;

use App\Models\RoleCategory;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Admin' => [
                'is_default' => true,
                'roles' => ['Super Admin', 'Admin']
            ],
            'Others' => [
                'is_default' => false,
                'roles' => [
                    'Department Role',
                ]
            ],
        ];

        $sortOrder = 1;
        foreach ($categories as $name => $data) {
            $category = RoleCategory::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'sort_order' => $sortOrder++,
                    'is_default' => $data['is_default']
                ]
            );

            // Update existing roles to belong to this category
            Role::whereIn('name', $data['roles'])
                ->update(['role_category_id' => $category->id]);
        }
    }
}
