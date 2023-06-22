<?php

namespace App\Services;

use App\Models\Pool;

class PoolService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        $pool = Pool::create($data);
        if (isset($data['employees']) && is_array($data['employees'])) {
            $pool->employees()->sync($data['employees']);
        }

        $pool->load('employees');

        return $pool;
    }

    public function update(array $data, mixed $pool)
    {
        $pool->update($data);

        if (isset($data['employees']) && is_array($data['employees'])) {
            $pool->employees()->sync($data['employees']);
        }

        $pool->refresh();
        $pool->load('employees');

        return $pool;
    }

    public function delete(mixed $pool)
    {
    }

    public function get(mixed $pool)
    {
        $pool->load('employees', 'branch');

        return $pool;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Pool::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })
        ->with('branch')
        ->get();
    }
}
