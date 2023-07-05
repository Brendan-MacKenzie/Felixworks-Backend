<?php

namespace App\Services;

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

        // Calculate the total existing commitments
        $totalCommitments = $posting->commitments()->sum('amount');

        // Calculate the remaining placements
        $remainingPlacements = $totalPlacements - $totalCommitments;

        // Validate the commitment amount
        if ($data['amount'] > $remainingPlacements) {
            throw new \Exception('The commitment amount cannot be higher than the remaining placements.');
        }

        // If validation passes, create the new commitment
        $commitment = Commitment::create($data);

        return $commitment->load('posting', 'posting.placements', 'agency');
    }

    public function update(array $data, mixed $commitment)
    {
    }

    public function delete(mixed $commitment)
    {
    }

    public function get(mixed $commitment)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
