<?php

namespace App\Services;

use App\Models\Workplace;

class WorkplaceService extends Service
{
    public function store(array $data)
    {
        return Workplace::create($data);
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
        return Workplace::FindOrFail($id);
    }

    public function list(int $perPage = 25, string $archiveStatus = 'active', string $query = null)
    {
    }
}
