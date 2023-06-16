<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Workplace;

class AddressService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        $workplacesData = $data['workplaces'] ?? [];
        unset($data['workplaces']);

        $address = Address::create($data);

        $workplaces = [];
        foreach ($workplacesData as $workplaceData) {
            $workplaces[] = new Workplace([
                'name' => $workplaceData['name'],
                'address_id' => $address->id,
            ]);
        }

        $address->workplaces()->saveMany($workplaces);
        $address->refresh();
        $address->load('workplaces');

        return $address;
    }

    public function update(array $data, mixed $address)
    {
        $address->update($data);

        $address->refresh();
        $address->load('workplaces');

        return $address;
    }

    public function delete(mixed $address)
    {
        $address->workplaces()->delete();
        $address->delete();
    }

    public function get(mixed $address)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
        $addresses = Address::query()->with('workplaces')->get();

        return $addresses;
    }
}
