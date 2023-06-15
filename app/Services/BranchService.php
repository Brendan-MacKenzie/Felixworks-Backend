<?php

namespace App\Services;

use App\Models\Branch;

class BranchService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        $branch = Branch::create($data);
        $branch->regions()->sync($data['regions']);
        $branch->refresh();
        $branch->load('regions');

        return $branch;
    }

    public function update(array $data, mixed $branch)
    {
        $branch->update($data);
        $branch->regions()->sync($data['regions']);
        $branch->refresh();
        $branch->load(['regions']);

        return $branch;
    }

    public function delete(mixed $branch)
    {
    }

    public function get(mixed $branch)
    {
        $branch->load([
            'addresses',
            'regions',
            'createdBy',
            'coordinators',
            'employees',
        ]);

        return $branch;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Branch::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }
}
