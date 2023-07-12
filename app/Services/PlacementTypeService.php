<?php

namespace App\Services;

use Exception;
use App\Models\Location;
use App\Models\PlacementType;
use Illuminate\Support\Facades\Auth;

class PlacementTypeService extends Service
{
    public function store(array $data)
    {
        $location = Location::findOrFail($data['location_id']);
        if ($location->client !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to add a placement type in this location.", 403);
        }

        return PlacementType::create($data);
    }

    public function update(array $data, mixed $placementType)
    {
    }

    public function delete(mixed $placementType)
    {
        if ($placementType->location_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to delete this placement type from this location.", 403);
        }

        // if placementType is linked to future postings, block.
        if ($this->checkFuturePostings($placementType)->count() > 0) {
            throw new Exception("This placement type can't be deleted, because it still has future postings.", 403);
        }

        $placementType->delete();
    }

    public function get(mixed $placementType)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    public function listByLocation(int $location, string $query = null)
    {
        return PlacementType::where('location_id', $location)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%');
            })
            ->get();
    }

    private function checkFuturePostings(PlacementType $placementType)
    {
        return $placementType
            ->placements()
            ->future()
            ->with([
                'postings' => function ($q) {
                    $q->future()->select('id');
                },
                'postings.agencies',
            ])
            ->get()
            ->flatMap(function ($placement) {
                return $placement->posting;
            })
            ->unique('id');
    }
}
