<?php

namespace App\Services;

use Exception;
use App\Models\Pool;
use App\Models\Branch;
use App\Enums\PoolType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PoolService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;
        $data['type'] = PoolType::Default;
        $branch = Branch::findOrFail($data['branch_id']);

        if ($branch->client_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to create this pool.", 403);
        }

        DB::beginTransaction();
        $pool = Pool::create($data);

        if (key_exists('employees', $data)) {
            $this->syncEmployees($data['employees'], $pool);
        }
        DB::commit();

        return $pool;
    }

    public function update(array $data, mixed $pool)
    {
        if ($pool->branch->client_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to update this pool.", 403);
        }

        DB::beginTransaction();
        $pool->update($data);
        $pool->refresh();

        if (key_exists('employees', $data)) {
            $this->syncEmployees($data['employees'], $pool);
        }

        DB::commit();

        return $pool;
    }

    public function delete(mixed $pool)
    {
        if ($pool->branch->client_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to delete this pool.", 403);
        }

        $pool->employees()->detach();
        $pool->delete();
    }

    public function get(mixed $pool)
    {
        $pool->load([
            'employees',
            'branch',
        ]);

        return $pool;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Pool::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })
        ->with('branch')
        ->get();
    }

    public function syncEmployees(array $employeeIds, Pool $pool)
    {
        // Check if employees are known in the branch
        $branch = $pool->branch;
        $branchEmployeesIds = $branch->employees->pluck('id')->all();
        if (count(array_diff($employeeIds, $branchEmployeesIds)) > 0) {
            throw new Exception('Some employees cannot be added to this pool.', 403);
        }

        $pool->employees()->sync($employeeIds);
    }
}
