<?php

namespace App\Services;

use Exception;
use App\Models\Address;
use App\Models\Workplace;
use App\Enums\AddressType;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use Illuminate\Support\Facades\Auth;

class WorkplaceService extends Service
{
    public function store(array $data)
    {
        if (key_exists('address_id', $data)) {
            $this->checkAddress($data['address_id']);
        }

        return Workplace::create($data);
    }

    public function update(array $data, mixed $workplace)
    {
        $this->checkAddress($workplace->address_id);

        $workplace->update($data);

        // if workplace is linked to future postings, call sync.
        SyncHelper::syncPostings(
            $this->checkFuturePostings($workplace),
            AgencyActionType::PostingUpdate
        );

        return $workplace;
    }

    public function delete(mixed $workplace)
    {
        $this->checkAddress($workplace->address_id);

        // if workplace is linked to future postings, block.
        if ($this->checkFuturePostings($workplace)->count() > 0) {
            throw new Exception("This workplace can't be deleted, because it still has future postings.", 403);
        }

        $workplace->delete();
    }

    public function get(mixed $workplace)
    {
        $workplace->load([
            'address',
        ]);

        return $workplace;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Workplace::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->get();
    }

    private function checkAddress(int $addressId)
    {
        $address = Address::find($addressId);

        if (!$address) {
            throw new Exception('Could not find address.', 404);
        }

        if ($address->type !== AddressType::WorkAddress) {
            throw new Exception('This address is not an workplace address.', 403);
        }

        if ($address->model->client_id !== Auth::user()->client_id) {
            throw new Exception("You don't have permission to manage this workplace.", 403);
        }
    }

    private function checkFuturePostings(Workplace $workplace)
    {
        return $workplace
            ->placements()
            ->future()
            ->with([
                'postings' => function ($q) {
                    $q->future()->select('id');
                },
                'postings.agencies',
            ])
            ->get()
            ->flatMap(function ($placement) {
                return $placement->posting;
            })
            ->unique('id');
    }
}
