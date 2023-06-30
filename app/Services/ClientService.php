<?php

namespace App\Services;

use App\Models\Client;
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

        return $client;
    }

    public function delete(mixed $client)
    {
    }

    public function get(mixed $client)
    {
        $client->load([
            'branches',
            'branches.address',
            'branches.coordinators',
            'branches.pools',
            'branches.regions',
        ]);

        return $client;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Client::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }
}
