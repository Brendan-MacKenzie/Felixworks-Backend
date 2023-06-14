<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
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
    }
}
