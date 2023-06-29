<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Posting;
use App\Models\Placement;

class PostingService extends Service
{
    public function store(array $data)
    {
        $repeatType = $data['repeat_type'] ?? null;
        $postingStartDate = $data['posting_start_date'];
        $postingEndDate = $data['posting_end_date'] ?? null;
        $placementsData = $data['placements'];

        // Empty array to store the created postings
        $createdPostings = [];

        // If the repeat type is set to never, create a single posting
        if ($repeatType === 0) {
            $postingData = $this->preparePostingData($data);
            $postingData['start_at'] = $postingStartDate;
            $posting = Posting::create($postingData);
            // Sync the list of agencies
            $posting->agencies()->sync($data['agencies']);
            // Sync the list of regions
            $posting->regions()->sync($data['regions']);
            // Create the placements for the posting
            $this->createPlacements($posting, $placementsData);
            $createdPostings[] = $posting;
        }

        // Create multiple postings based on repeat type, start date, and end date
        elseif (in_array($repeatType, [1, 2], true) && $postingStartDate && $postingEndDate) {
            $startDate = Carbon::parse($postingStartDate);
            $endDate = Carbon::parse($postingEndDate);

            while ($startDate <= $endDate) {
                $postingData = $this->preparePostingData($data);
                $postingData['start_at'] = $startDate->format('Y-m-d H:i:s');
                $posting = Posting::create($postingData);
                // Sync the list of agencies
                $posting->agencies()->sync($data['agencies']);
                // Sync the list of regions
                $posting->regions()->sync($data['regions']);
                // Create the placements for the posting
                $this->createPlacements($posting, $placementsData);
                $createdPostings[] = $posting;

                if ($repeatType === 1) {
                    // Add 1 day for daily repeat
                    $startDate->addDay();
                } else {
                    // Add 1 week for weekly repeat
                    $startDate->addWeek();
                }
            }
        }

        // Load the relationships for the created postings
        foreach ($createdPostings as $posting) {
            $posting->load('placements.placementType', 'placements.workplace', 'regions', 'agencies', 'address');
        }

        // Return the created posting with the required relationships
        return $createdPostings;
    }

    public function preparePostingData(array $data)
    {
        $postingData = [
            'name' => $data['name'],
            'address_id' => $data['address_id'],
            'dresscode' => $data['dresscode'] ?? null,
            'briefing' => $data['briefing'] ?? null,
            'information' => $data['information'] ?? null,
            'cancelled_at' => $data['cancelled_at'] ?? null,
            'agencies' => $data['agencies'],
            'regions' => $data['regions'],
            'created_by' => auth()->user()->id,
        ];

        return $postingData;
    }

    public function preparePlacementData(array $data)
    {
        $placementData = [
            'posting_id' => $data['posting_id'],
            'workplace_id' => $data['workplace_id'],
            'placement_type_id' => $data['placement_type_id'],
            'report_at' => $data['report_at'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'created_by' => auth()->user()->id,
        ];

        return $placementData;
    }

    public function createPlacements(Posting $posting, array $placementsData): void
    {
        foreach ($placementsData as $placementData) {
            $placementData['posting_id'] = $posting->id;

            $postingDate = $posting->start_at->format('Y-m-d');

            $placementStartDateTime = $postingDate.' '.Carbon::parse($placementData['start_at'])->format('H:i:s');
            $placementEndDateTime = $postingDate.' '.Carbon::parse($placementData['end_at'])->format('H:i:s');
            $placementReportDateTime = $postingDate.' '.Carbon::parse($placementData['report_at'])->format('H:i:s');

            $placement = Placement::create($this->preparePlacementData($placementData));
            $placement->start_at = Carbon::parse($placementStartDateTime);
            $placement->end_at = Carbon::parse($placementEndDateTime);
            $placement->report_at = Carbon::parse($placementReportDateTime);
            $placement->save();
        }
    }

    public function update(array $data, mixed $posting)
    {
    }

    public function delete(mixed $posting)
    {
    }

    public function get(mixed $posting)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}