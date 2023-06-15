<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RegionSeeder::class);

        $this->call(RoleAndPermissionSeeder::class);

        $this->call(SectorSeeder::class);

        $this->call(ClientSeeder::class);

        $this->call(AgencySeeder::class);

        $this->call(UserSeeder::class);

        $this->call(PostingSeeder::class);

        $this->call(RegionSeeder::class);
    }
}
