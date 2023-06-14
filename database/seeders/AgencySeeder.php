<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Office;
use App\Models\Region;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all()->pluck('id')->all();

        $agency = Agency::updateOrCreate([
            'name' => 'Felix Uitzendbureau',
            'full_name' => 'Felix Uitzendbureau BV',
            'code' => 'FELX',
            'brand_color' => '#ffff',
        ]);

        $agency->regions()->attach($regions);

        $office = Office::updateOrCreate([
            'agency_id' => $agency->id,
            'name' => 'Kantoor 1',
            'description' => 'Dit is een kantoor',
            'website' => 'https://felixworks.nl',
            'phone' => '06123456789',
            'street_name' => 'Fellenoord',
            'number' => 200,
            'zip_code' => '4907BG',
            'city' => 'Eindhoven',
            'country' => 'Nederland',
        ]);

        $office->regions()->attach($regions);
    }
}
