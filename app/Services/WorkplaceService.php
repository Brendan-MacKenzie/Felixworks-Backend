<?php

namespace App\Services;

use Exception;
use App\Models\Address;
use App\Models\Workplace;
use App\Enums\AddressType;
use Illuminate\Support\Facades\Auth;

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
        $this->checkAddress($workplace->address_id);

        $workplace->update($data);

        return $workplace;
    }

    public function delete(mixed $workplace)
    {
        $this->checkAddress($workplace->address_id);
        $workplace->delete();
    }

    public function get(mixed $workplace)
    {
        $workplace->load([
            'address',
        ]);

        return $workplace;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Workplace::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->get();
    }

    private function checkAddress(int $addressId)
    {
        $address = Address::find($addressId);

        if (!$address) {
            throw new Exception('Could not find address.', 404);
        }

        if ($address->type !== AddressType::Default) {
            throw new Exception('This address is not an workplace address.', 403);
        }

        if ($address->model->client_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to manage this workplace.", 403);
        }
    }
}
