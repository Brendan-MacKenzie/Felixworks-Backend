<?php

namespace App\Services;

use App\Models\Client;

class ClientService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        return Client::create($data);
    }

    public function update(array $data, mixed $client)
    {
    }

    public function delete(mixed $client)
    {
    }

    public function get(mixed $client)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Client::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }
}
