<?php

namespace App\Services;

use App\Models\Region;

class RegionService extends Service
{
    public function store(array $data)
    {
    }

    public function update(array $data, int $id)
    {
    }

    public function delete(int $id)
    {
    }

    public function get(int $id)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Region::where('name', 'like', "%{$query}%")->get();
    }
}
