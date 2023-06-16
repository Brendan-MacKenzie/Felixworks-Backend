<?php

namespace App\Services;

use App\Models\Placement;

class PlacementService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        $placement = Placement::create($data);
        $placement = Placement::with([
            'placementType',
            'employee',
            'workplace',
            'posting',
        ])->findOrFail($placement->id);

        return $placement;  
    }

    public function update(array $data, mixed $placement)
    {
    }

    public function delete(mixed $placement)
    {
    }

    public function get(mixed $placement)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
