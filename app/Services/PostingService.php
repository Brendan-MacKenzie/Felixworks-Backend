<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Agency;
use App\Models\Address;
use App\Models\Posting;
use App\Enums\AddressType;
use App\Enums\PlacementStatus;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use App\Models\Scopes\ActiveScope;
use App\Services\Sync\RedisHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class PostingService extends Service
{
    public function store(array $data)
    {
        $repeatType = $data['repeat_type'] ?? null;
        $postingStartDate = $data['posting_start_date'];
        $postingEndDate = $data['posting_end_date'] ?? null;

        // Empty array to store the created postings
        $createdPostings = collect();

        $workAddress = Address::findOrFail($data['address_id']);
        if ($workAddress->type !== AddressType::Default) {
            throw new Exception('Address is not a workadress.', 403);
        }

        $postingData = [
            'name' => $data['name'],
            'address_id' => $data['address_id'],
            'dresscode' => $data['dresscode'] ?? null,
            'briefing' => $data['briefing'] ?? null,
            'information' => $data['information'] ?? null,
            'agencies' => $data['agencies'],
            'regions' => $data['regions'],
            'created_by' => Auth::user()->id,
        ];

        if ($repeatType) {
            // Create multiple postings based on repeat type, start date, and end date
            $startDate = Carbon::parse($postingStartDate);
            $endDate = Carbon::parse($postingEndDate);

            while ($startDate <= $endDate) {
                $postingData['start_at'] = $startDate->format('Y-m-d H:i:s');
                $posting = Posting::create($postingData);
                // Sync the list of agencies
                $posting->agencies()->sync($data['agencies']);
                // Sync the list of regions
                $posting->regions()->sync($data['regions']);
                $createdPostings->push($posting);

                // Make action job for agencies.
                SyncHelper::syncInBulk($posting->agencies, $posting, AgencyActionType::PostingUpdate, $posting->id);

                if ($repeatType === 1) {
                    // Add 1 day for daily repeat
                    $startDate->addDay();
                } else {
                    // Add 1 week for weekly repeat
                    $startDate->addWeek();
                }
            }
        } else {
            // If the repeat type is set to never, create a single posting
            $postingData['start_at'] = $postingStartDate;
            $posting = Posting::create($postingData);
            // Sync the list of agencies
            $posting->agencies()->sync($data['agencies']);
            // Sync the list of regions
            $posting->regions()->sync($data['regions']);
            $createdPostings->push($posting);

            // Make action job for agencies.
            SyncHelper::syncInBulk($posting->agencies, $posting, AgencyActionType::PostingUpdate, $posting->id);
        }

        // Return the created posting with the required relationships
        return $createdPostings;
    }

    public function update(array $data, mixed $posting)
    {
        $posting->update($data);

        if (isset($data['agencies'])) {
            $posting->agencies()->sync($data['agencies']);
        }

        if (isset($data['regions'])) {
            $posting->regions()->sync($data['regions']);
        }
        $posting->refresh();

        $posting->load('placements.placementType', 'placements.workplace', 'regions', 'agencies', 'workAddress');

        // Make action job for agencies.
        SyncHelper::syncInBulk($posting->agencies, $posting, AgencyActionType::PostingUpdate, $posting->id);

        return $posting;
    }

    public function delete(mixed $posting)
    {
    }

    public function get(mixed $posting)
    {
        $posting->load(
            'placements.placementType',
            'placements.workplace',
            'placements.employee',
            'placements.employee.agency',
            'placements.declarations',
            'regions',
            'agencies',
            'workAddress',
            'commitments'
        );

        return $posting;
    }

    public function getBulk(array $postingIds)
    {
        return Posting::whereIn('id', $postingIds)
            ->with([
                'placements.placementType',
                'placements.workplace',
                'placements.employee',
                'regions',
                'agencies',
                'workAddress',
            ])
            ->get();
    }

    public function list(int $perPage = 25, string $query = null, bool $cancelledOnly = false)
    {
        return Posting::with([
            'placements.placementType',
            'placements.workplace',
            'placements.employee',
            'placements.employee.agency',
            'placements.declarations',
            'regions',
            'agencies',
            'workAddress',
            'commitments',
        ])
            ->when($cancelledOnly, function ($q) {
                $q->withoutGlobalScope(ActiveScope::class)
                    ->cancelled();
            })
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%');
            })
            ->paginate($perPage);
    }

    public function cancel(Posting $posting)
    {
        // Check if any related placements have associated employees and if cancellation takes place within the cancel hours.
        if (
            $posting->placements()->has('employee')->exists() &&
            $posting->start_at->diffInHours(Carbon::now()) <= config('app.CANCEL_HOURS')
        ) {
            throw new Exception('The posting cannot be cancelled because it starts within the next '.config('app.CANCEL_HOURS').' hours.');
        }

        $posting->cancelled_at = Carbon::now();
        $posting->save();
        $posting->placements()->update(['status' => PlacementStatus::Cancelled]);

        // Make action job for agencies.
        SyncHelper::syncInBulk($posting->agencies, $posting, AgencyActionType::PostingRemoved, $posting->id);

        return $posting;
    }

    public function syncAgency(Agency $agency, Posting $posting)
    {
        $redisPosting = Redis::get('posting-'.$posting->id);

        if ($redisPosting) {
            return json_decode($redisPosting);
        }

        // Get posting with all relations
        $posting->load([
            'workAddress',
            'workAddress.model',
            'workAddress.model.address',
            'workAddress.model.client',
            'placements',
            'placements.workplace',
            'placements.placementType',
            'placements.employee',
            'placements.employee',
            'regions',
        ]);

        RedisHelper::syncPosting($posting, false);

        $posting->placements = $posting->placements->filter(function ($placement) use ($agency) {
            return is_null($placement->employee_id) || $placement->employee->agency_id == $agency->id;
        });

        $posting->commitments = $posting->commitments->filter(function ($commitment) use ($agency) {
            return $commitment->agency_id == $agency->id;
        });

        return $posting;
    }
}
