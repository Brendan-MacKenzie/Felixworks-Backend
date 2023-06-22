<?php

namespace App\Services;

use Exception;
use App\Models\Branch;
use App\Models\PlacementType;
use Illuminate\Support\Facades\Auth;

class PlacementTypeService extends Service
{
    public function store(array $data)
    {
        $branch = Branch::findOrFail($data['branch_id']);
        if ($branch->client !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to add a placement type in this branch.", 403);
        }

        return PlacementType::create($data);
    }

    public function update(array $data, mixed $placementType)
    {
    }

    public function delete(mixed $placementType)
    {
        if ($placementType->branch_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to delete this placement type from this branch.", 403);
        }

        $placementType->delete();
    }

    public function get(mixed $placementType)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    public function listByBranch(int $branch, string $query = null)
    {
        return PlacementType::where('branch_id', $branch)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%');
            })
            ->get();
    }
}
