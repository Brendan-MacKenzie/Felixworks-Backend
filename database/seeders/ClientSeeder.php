<?php

namespace Database\Seeders;

use App\Models\Pool;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Region;
use App\Enums\PoolType;
use App\Models\Address;
use App\Models\Workplace;
use App\Enums\AddressType;
use App\Models\PlacementType;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::inRandomOrder()->limit(10)->pluck('id')->all();

        $client = Client::updateOrCreate([
            'name' => 'Klant 1',
        ]);

        $address = Address::updateOrCreate([
            'name' => 'Vestigingsadres 1',
            'type' => AddressType::Branch,
            'street_name' => 'Dillehof',
            'number' => 123,
            'zip_code' => '1234AB',
            'city' => 'Breda',
            'country' => 'Nederland',
        ]);

        $branch = Branch::updateOrCreate([
            'name' => 'Vestiging 1',
            'client_id' => $client->id,
            'address_id' => $address->id,
        ]);

        $address = Address::updateOrCreate([
            'name' => 'Werkadres 1',
            'type' => AddressType::Default,
            'street_name' => 'Fellenoord',
            'number' => 200,
            'zip_code' => '4907BG',
            'city' => 'Eindhoven',
            'country' => 'Nederland',
        ]);

        $branch->regions()->attach($regions);
        $branch->addresses()->attach($address);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Restaurant',
            'address_id' => $address->id,
        ]);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Receptie',
            'address_id' => $address->id,
        ]);

        $workplace = Workplace::updateOrCreate([
            'name' => 'Winkel',
            'address_id' => $address->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Winkel medewerker',
            'branch_id' => $branch->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Keukenhulp',
            'branch_id' => $branch->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Housekeeping',
            'branch_id' => $branch->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Afwas medewerker',
            'branch_id' => $branch->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Bar medewerker',
            'branch_id' => $branch->id,
        ]);

        $placementType = PlacementType::updateOrCreate([
            'name' => 'Bedieningsmedewerker',
            'branch_id' => $branch->id,
        ]);

        $pool = Pool::updateOrCreate([
            'name' => 'Favorieten',
            'type' => PoolType::Default,
            'branch_id' => $branch->id,
        ]);
    }
}
