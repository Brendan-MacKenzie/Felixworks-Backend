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
        $branch = Branch::findOrFail($id);
        $branch->fill($data);
        $branch->save();

        // Sync the regions
        $branch->regions()->sync($data['regions']);

        return Branch::with('regions')->find($branch->id);
    }

    public function delete(int $id)
    {
    }

    public function get(int $id, bool $withArchived = false)
    {
        $query = Branch::query()
        ->with('addresses', 'regions', 'coordinators', 'employees');

        $branch = $query->findOrFail($id);

        return $branch;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Branch::where('name', 'like', "%{$query}%")->get();
    }
}
