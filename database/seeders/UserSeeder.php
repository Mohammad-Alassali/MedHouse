<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'SuperAdmin',
            'phone_number' => '0987654321',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
        ]);
    }
}
