<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sector = Sector::updateOrCreate([
            'name' => 'Horeca',
        ]);

        $sector = Sector::updateOrCreate([
            'name' => 'Hotel',
        ]);
    }
}
