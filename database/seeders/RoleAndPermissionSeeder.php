<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $client = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'api',
        ]);

        $agent = Role::firstOrCreate([
            'name' => 'agent',
            'guard_name' => 'api',
        ]);
    }
}
