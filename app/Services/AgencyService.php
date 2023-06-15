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

        return $agency;
    }

    public function update(array $data, int $id)
    {
        $workplace = $this->get($id);

        $workplace->update($data);

        return $workplace;
    }

    public function delete(int $id)
    {
    }

    public function get(int $id, bool $withArchived = false)
    {
        return Agency::FindOrFail($id);
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Agency::where('name', 'like', "%{$query}%")->get();
    }
}
