<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;
        $data['client_id'] = Auth::user()->client_id;

        $branch = Branch::create($data);
        $branch->regions()->sync($data['regions']);

        return $branch;
    }

    public function update(array $data, mixed $branch)
    {
        $branch->update($data);
        $branch->regions()->sync($data['regions']);
        $branch->refresh();

        return $branch;
    }

    public function delete(mixed $branch)
    {
    }

    public function get(mixed $branch)
    {
        $branch->load([
            'address',
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
