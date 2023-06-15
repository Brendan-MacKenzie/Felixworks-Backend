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

    public function update(array $data, int $id)
    {
    }

    public function delete(int $id)
    {
    }

    public function get(int $id, bool $withArchived = false)
    {
        //todo
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Client::where('name', 'like', "%{$query}%")->paginate($perPage);
    }
}
