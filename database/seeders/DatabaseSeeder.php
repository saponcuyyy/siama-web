<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@siama.sch.id',
            'password' => bcrypt('password'), // You should change this in production
        ]);

        $admin->assignRole('super_admin');
    }
}
