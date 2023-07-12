<?php

namespace App\Services;

use App\Models\Client;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use Illuminate\Support\Facades\Auth;

class ClientService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        return Client::create($data);
    }

    public function update(array $data, mixed $client)
    {
        $client->update($data);

        // if location is linked to future postings, call sync.
        SyncHelper::syncPostings(
            $this->checkFuturePostings($client),
            AgencyActionType::PostingUpdate
        );

        return $client;
    }

    public function delete(mixed $client)
    {
    }

    public function get(mixed $client)
    {
        $client->load([
            'locations',
            'locations.address',
            'locations.coordinators',
            'locations.pools',
            'locations.regions',
        ]);

        return $client;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Client::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }

    private function checkFuturePostings(Client $client)
    {
        return $client
            ->locations()
            ->whereHas('workAddresses', function ($q) {
                $q->whereHas('postings', function ($q) {
                    $q->future()->select('id', 'address_id');
                });
            })
            ->with([
                'workAddresses:id,model_type,model_id',
                'workAddresses.postings' => function ($q) {
                    $q->future()->select('id', 'address_id');
                },
                'workAddresses.postings.agencies',
            ])
            ->get()
            ->flatMap(function ($location) {
                return $location->workAddresses;
            })
            ->flatMap(function ($workAddress) {
                return $workAddress->postings;
            });
    }
}
