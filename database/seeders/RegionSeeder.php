<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the contents of the cities.json file from the local disk
        $json = Storage::disk('local')->get('cities.json');

        // Decode the JSON data into an associative array
        $cities = json_decode($json, true);

        // Create/update the Netherlands and retrieve its ID
        $countryId = $this->createRegion('Netherlands');

        // Loop through the cities and create/update regions
        foreach ($cities as $city) {
            $regionName = $city['admin_name'];
            $cityName = $city['city'];

            // Create/update region and retrieve its ID
            $regionId = $this->createRegion($regionName, $countryId);

            // Create city with the region's ID as parent_id
            $this->createCity($cityName, $regionId);
        }
    }

    private function createRegion($name, $parentId = null)
    {
        // Create/update region and retrieve its ID
        $region = Region::updateOrCreate(
            ['name' => $name, 'parent_id' => $parentId]
        );

        return $region->id;
    }

    private function createCity($name, $parentId)
    {
        Region::updateOrCreate(
            ['name' => $name, 'parent_id' => $parentId]
        );
    }
}
