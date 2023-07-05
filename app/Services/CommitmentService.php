<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Posting;
use App\Models\Commitment;
use Illuminate\Support\Facades\Auth;

class CommitmentService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        // Get posting
        $posting = Posting::find($data['posting_id']);

        // Calculate the total number of placements
        $totalPlacements = $posting->placements()->count();

        // Check if a commitment for the given agency and posting already exists
        $existingCommitment = $posting->commitments()->where('agency_id', $data['agency_id'])->first();

        if ($existingCommitment) {
            // If a commitment already exists, calculate the total existing commitments excluding the existing commitment
            $totalCommitments = $posting->commitments()->where('id', '!=', $existingCommitment->id)->sum('amount');
        } else {
            // If no commitment exists, calculate the total existing commitments
            $totalCommitments = $posting->commitments()->sum('amount');
        }

        // Calculate the remaining placements
        $remainingPlacements = $totalPlacements - $totalCommitments;

        // Validate the commitment amount
        if ($data['amount'] > $remainingPlacements) {
            throw new \Exception('The commitment amount cannot be higher than the remaining placements.');
        }

        // If a commitment already exists, update it
        if ($existingCommitment) {
            unset($data['created_by']);
            $existingCommitment->update($data);

            return $existingCommitment->load('posting', 'posting.placements', 'agency');
        }

        // If no commitment exists, create a new one
        $commitment = Commitment::create($data);

        return $commitment->load('posting', 'posting.placements', 'agency');
    }

    public function update(array $data, mixed $commitment)
    {
        // Get posting
        $posting = $commitment->posting;

        // Calculate the total number of placements
        $totalPlacements = $posting->placements()->count();

        // Calculate the total existing commitments excluding the current commitment
        $totalCommitments = $posting->commitments()->where('id', '!=', $commitment->id)->sum('amount');

        // Calculate the remaining placements
        $remainingPlacements = $totalPlacements - $totalCommitments;

        // Validate the commitment amount
        if ($data['amount'] > $remainingPlacements) {
            throw new \Exception('The commitment amount cannot be higher than the remaining placements.');
        }

        // If validation passes, update the commitment
        $commitment->update($data);

        return $commitment->load('posting', 'posting.placements', 'agency');
    }

    public function delete(mixed $commitment)
    {
        $posting = $commitment->posting;

        // Check if the posting has already started
        if ($posting->start_at->isPast()) {
            throw new \Exception('Commitments on postings that have already started cannot be deleted.');
        }

        // Check if the posting starts within the next 24 hours
        if ($posting->start_at->diffInHours(Carbon::now()) <= 24) {
            throw new \Exception('Commitments on postings that are starting within the next 24 hours cannot be deleted.');
        }

        // If none of the conditions are met, delete the commitment
        $commitment->delete();
    }

    public function get(mixed $commitment)
    {
        $commitment->load(
            'posting',
            'posting.placements',
            'posting.placements.placementType',
            'posting.placements.workplace',
            'posting.placements.employee',
            'agency'
        );

        return $commitment;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        // If a query is provided, search for the query in the posting and agency names
        return Commitment::with('posting', 'agency')
        ->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->whereHas('posting', function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', '%'.$query.'%');
            })->orWhereHas('agency', function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', '%'.$query.'%');
            });
        })->paginate($perPage);
    }
}
