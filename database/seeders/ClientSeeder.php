<?php

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Enums\PoolType;
use App\Models\Address;
use App\Models\Client;
use App\Models\PlacementType;
use App\Models\Pool;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Workplace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all()->pluck('id')->all();
        $sectors = Sector::all()->pluck('id')->all();

        $client = Client::updateOrCreate([
            'name' => 'Klant 1',
        ]);

        $address = Address::updateOrCreate([
            'client_id' => $client->id,
            'name' => 'Adres 1',
            'type' => AddressType::Unspecified,
            'street_name' => 'Fellenoord',
            'number' => 200,
            'zip_code' => '4907BG',
            'city' => 'Eindhoven',
            'country' => 'Nederland',
        ]);

        $client->regions()->attach($regions);
        $client->sectors()->attach($sectors);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Restaurant',
            'client_id' => $client->id
        ]);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Receptie',
            'client_id' => $client->id
        ]);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Winkel',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Winkel medewerker',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Keukenhulp',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Housekeeping',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Afwas medewerker',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Bar medewerker',
            'client_id' => $client->id
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Bedieningsmedewerker',
            'client_id' => $client->id
        ]);

        $pool = Pool::updateOrCreate([
            'name' => 'Favorieten',
            'type' => PoolType::Default,
            'client_id' => $client->id, 
        ]);
    }
}
