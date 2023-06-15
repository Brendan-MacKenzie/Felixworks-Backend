<?php

namespace App\Services;

use Exception;
use App\Models\Address;
use App\Models\Workplace;
use App\Enums\AddressType;

class WorkplaceService extends Service
{
    public function store(array $data)
    {
        if (key_exists('address_id', $data)) {
            $this->checkAddress($data['address_id']);
        }

        return Workplace::create($data);
    }

    public function update(array $data, mixed $workplace)
    {
        if (key_exists('address_id', $data)) {
            $this->checkAddress($data['address_id']);
        }

        $workplace->update($data);

        return $workplace;
    }

    public function delete(mixed $workplace)
    {
        $workplace->delete();
    }

    public function get(mixed $workplace)
    {
        return $workplace;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Workplace::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->get();
    }

    private function checkAddress(int $addressId)
    {
        $address = Address::findOrFail($addressId);

        if ($address->type !== AddressType::Default) {
            throw new Exception('This address is not an workplace address.', 403);
        }
    }
}
