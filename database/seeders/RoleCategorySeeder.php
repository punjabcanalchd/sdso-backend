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
            'Applicant' => [
                'is_default' => false,
                'roles' => ['Applicant Users']
            ],
            'Admin' => [
                'is_default' => true,
                'roles' => ['Super Admin', 'Admin', 'Editor', 'Mobile', 'Offline permissions']
            ],
            'Permission' => [
                'is_default' => false,
                'roles' => ['RO', 'SO', 'AO1', 'AO2', 'AO3', 'DSO']
            ],
            'Account' => [
                'is_default' => false,
                'roles' => ['Accountant', 'Account Executive', 'Accounts Division']
            ],
            'Reports' => [
                'is_default' => false,
                'roles' => ['MIS Report', 'Mis Report Accountant']
            ],
            'Others' => [
                'is_default' => false,
                'roles' => [
                    'Water Resource User',
                    'Chief Administrator Officer',
                    'Principal Secretary, Local Government',
                    'Canals Administration',
                    'Member Secretary, PPCB, Nabha Road, Patiala',
                    'Directorate of Groundwater designated as Implementing Agency',
                    'Deputy Commissioner',
                    'XEN'
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
