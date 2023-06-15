<?php

namespace App\Services;

use App\Models\Agency;

class AgencyService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        if (key_exists('logo_id', $data)) {
            // Todo
        }

        $agency = Agency::create($data);

        if (key_exists('regions', $data)) {
            $agency->regions()->sync($data['regions']);
        }

        $agency->load('regions');

        return $agency;
    }

    public function update(array $data, mixed $agency)
    {
        $agency->update($data);

        if (key_exists('regions', $data)) {
            $agency->regions()->sync($data['regions']);
        }

        $agency->refresh();
        $agency->load('regions');

        return $agency;
    }

    public function delete(mixed $agency)
    {
    }

    public function get(mixed $agency)
    {
        $agency->load([
            'offices',
            'commitments',
            'users',
            'employees',
            'regions',
        ]);

        return $agency;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Agency::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->with('regions')->paginate($perPage);
    }
}
