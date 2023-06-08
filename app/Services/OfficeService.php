<?php

namespace App\Services;

use App\Models\Office;

class OfficeService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        return Office::create($data);
    }

    public function update(array $data, int $id)
    {
        $office = $this->get($id);

        $office->update($data);

        return $office;
    }

    public function delete(int $id)
    {
        $office = $this->get($id);

        $office->delete();
    }

    public function get(int $id, bool $withArchived = false)
    {
        return Office::FindOrFail($id);
    }

    public function list(int $perPage = 25, string $archiveStatus = 'active', string $query = null)
    {
    }
}
