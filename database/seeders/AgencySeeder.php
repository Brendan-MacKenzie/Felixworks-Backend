<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Office;
use App\Models\Region;
use App\Models\Address;
use App\Enums\AddressType;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::inRandomOrder()->limit(10)->pluck('id')->all();

        $agency = Agency::updateOrCreate([
            'name' => 'Felix Uitzendbureau',
            'full_name' => 'Felix Uitzendbureau BV',
            'brand_color' => '#ffff',
            'email' => 'info@felixworks.test',
            'base_rate' => 2500,
            'api_key' => password_hash('this_is_a_test_api_key', PASSWORD_BCRYPT),
            'ip_address' => '172.19.0.1',
            'webhook' => 'https://planworks.test/felix',
            'webhook_key' => password_hash('this_is_a_test_webhook_key', PASSWORD_BCRYPT),
        ]);

        $agency->regions()->attach($regions);

        $address = Address::updateOrCreate([
            'name' => $agency->full_name,
            'type' => AddressType::Office,
            'street_name' => 'Keukenstraat',
            'number' => 326,
            'zip_code' => '6789MN',
            'city' => 'Rotterdam',
            'country' => 'Nederland',
        ]);

        $office = Office::updateOrCreate([
            'agency_id' => $agency->id,
            'name' => 'Kantoor 1',
            'description' => 'Dit is een kantoor',
            'website' => 'https://felixworks.nl',
            'phone' => '06123456789',
        ]);

        $office->address()->save($address);
        $office->regions()->attach($regions);
    }
}
