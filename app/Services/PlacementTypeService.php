<?php

namespace App\Services;

use App\Models\PlacementType;

class PlacementTypeService extends Service
{
    public function store(array $data)
    {
        $placementType = PlacementType::create($data);
        // $placementType = PlacementType::with([
        //     'branch',
        // ])->findOrFail($placementType->id);

        return $placementType;
    }

    public function update(array $data, mixed $placementType)
    {
    }

    public function delete(mixed $placementType)
    {
    }

    public function get(mixed $placementType)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}