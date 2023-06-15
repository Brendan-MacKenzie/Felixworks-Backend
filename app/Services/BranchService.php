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

        return Branch::with('regions')->find($branch->id);
    }

    public function update(array $data, int $id)
    {
    }

    public function delete(int $id)
    {
    }

    public function get(int $id, bool $withArchived = false)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
