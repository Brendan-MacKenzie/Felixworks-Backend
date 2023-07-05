<?php

namespace App\Services;

use Exception;
use App\Models\Branch;
use App\Models\Office;
use App\Models\Address;
use App\Models\Workplace;
use App\Enums\AddressType;
use Illuminate\Support\Facades\Auth;

class AddressService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        $workplacesData = $data['workplaces'] ?? [];
        unset($data['workplaces']);

        if (
            key_exists('model_type', $data) &&
            key_exists('model_id', $data) &&
            $data['model_type'] &&
            $data['model_id']
        ) {
            $model = app($data['model_type']);
            $object = $model->find($data['model_id']);

            if (!$object) {
                throw new Exception('Could not find model.', 500);
            }
            unset($data['model_type']);
            unset($data['model_id']);
        }

        $address = Address::create($data);

        $workplaces = [];
        foreach ($workplacesData as $workplaceData) {
            $workplaces[] = new Workplace([
                'name' => $workplaceData['name'],
                'address_id' => $address->id,
            ]);
        }

        if (count($workplaces) > 0) {
            $address->workplaces()->saveMany($workplaces);
        }

        if (isset($object)) {
            $this->linkModel($address->id, $object);
        }

        $address->refresh();

        return $address;
    }

    public function update(array $data, mixed $address)
    {
        $address->update($data);

        $address->refresh();

        return $address;
    }

    public function delete(mixed $address)
    {
        $model = $address->model;
        if ($model && $model->address_id == $address->id) {
            throw new Exception('This address is still linked.', 403);
        }

        $address->workplaces()->delete();
        $address->delete();
    }

    public function get(mixed $address)
    {
        $address->load([
            'branch',
            'office',
            'model',
            'workplaces',
        ]);
    }

    public function list(int $perPage = 25, string $query = null)
    {
        $addresses = Address::with('workplaces')->get();

        return $addresses;
    }

    public function linkModel(int $addressId, mixed $model)
    {
        $address = Address::findOrFail($addressId);

        if ($model instanceof Office && $address->type !== AddressType::Office) {
            throw new Exception("You can't link an office on this address.", 500);
        }

        if ($model instanceof Branch && $address->type !== AddressType::Branch) {
            throw new Exception("You can't link an branch on this address.", 500);
        }

        $address->model()->save($model);

        return $address;
    }

    public function unlinkModel(Address $address)
    {
        $address->model()->save();
        $this->delete($address);
    }
}
