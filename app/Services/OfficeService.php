<?php

namespace App\Services;

use Exception;
use App\Models\Office;
use App\Models\Address;
use App\Enums\AddressType;

class OfficeService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        if (key_exists('address_id', $data)) {
            $this->checkAddress($data['address_id']);
        }

        return Office::create($data);
    }

    public function update(array $data, mixed $office)
    {
        $office->update($data);

        return $office;
    }

    public function delete(mixed $office)
    {
        $office->delete();
    }

    public function get(mixed $office)
    {
        return $office;
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    private function checkAddress(int $addressId)
    {
        $address = Address::findOrFail($addressId);

        if ($address->type !== AddressType::Office) {
            throw new Exception('This address is not an office address.', 403);
        }
    }
}
