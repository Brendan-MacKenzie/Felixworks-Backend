<?php

namespace App\Services;

use App\Models\Region;

class RegionService extends Service
{
    public function store(array $data)
    {
    }

    public function update(array $data, mixed $region)
    {
    }

    public function delete(mixed $region)
    {
    }

    public function get(mixed $region)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Region::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->get();
    }
}
