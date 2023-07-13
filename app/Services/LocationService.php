<?php

namespace App\Services;

use App\Models\Location;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use Illuminate\Support\Facades\Auth;

class LocationService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;
        $data['client_id'] = Auth::user()->client_id;

        $location = Location::create($data);
        $location->regions()->sync($data['regions']);
        $location->users()->attach(Auth::user());

        return $location;
    }

    public function update(array $data, mixed $location)
    {
        $location->update($data);
        $location->regions()->sync($data['regions']);
        $location->refresh();

        // if location is linked to future postings, call sync.
        SyncHelper::syncPostings(
            $this->checkFuturePostings($location),
            AgencyActionType::PostingUpdate
        );

        return $location;
    }

    public function delete(mixed $location)
    {
    }

    public function get(mixed $location)
    {
        $location->load([
            'address',
            'regions',
            'createdBy',
            'coordinators',
            'employees',
        ]);

        return $location;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Location::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }

    private function checkFuturePostings(Location $location)
    {
        return $location
            ->workAddresses()
            ->with([
                'postings' => function ($q) {
                    $q->future()->select('id', 'address_id');
                },
                'postings.agencies',
            ])
            ->get()
            ->flatMap(function ($address) {
                return $address->postings;
            });
    }
}
