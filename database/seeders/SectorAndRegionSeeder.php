<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorAndRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $region = Region::updateOrCreate([
            'name' => 'Eindhoven',
        ]);

        $region = Region::updateOrCreate([
            'name' => 'Utrecht',
        ]);

        $region = Region::updateOrCreate([
            'name' => 'Amsterdam',
        ]);
        
        $sector = Sector::updateOrCreate([
            'name' => 'Horeca',
        ]);

        $sector = Sector::updateOrCreate([
            'name' => 'Hotel',
        ]);
    }
}
