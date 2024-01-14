<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    /**
     * @return void
     */
    public function run(): void
    {
        $permissions = [
            'delete_company',
            'add_company',
            'update_company',
            'change_status_of_request',
            'add_new_admin',
            'add_product',
            'delete_product',
            'update_product'
        ];

        foreach ($permissions as $permission) {
            Permission::query()->create([
                'type' => $permission
            ]);
        }

    }
}
