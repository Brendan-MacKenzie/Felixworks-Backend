<?php

namespace App\Services;

use App\Models\Branch;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use Illuminate\Support\Facades\Auth;

class BranchService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;
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

        // if branch is linked to future postings, call sync.
        SyncHelper::syncPostings(
            $this->checkFuturePostings($branch),
            AgencyActionType::PostingUpdate
        );

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
        return Branch::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->paginate($perPage);
    }

    private function checkFuturePostings(Branch $branch)
    {
        return $branch
            ->workAddresses()
            ->with([
                'postings' => function ($q) {
                    $q->future()->select('id', 'address_id');
                },
                'postings.agencies',
            ])
            ->get()
            ->flatMap(function ($address) {
                return $address->postings;
            });
    }
}
