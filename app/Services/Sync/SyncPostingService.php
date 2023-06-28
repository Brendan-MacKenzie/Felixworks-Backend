<?php

namespace App\Services\Sync;

use App\Models\Agency;
use App\Models\Posting;
use Illuminate\Support\Facades\Redis;

class SyncPostingService
{
    public function get(Agency $agency, Posting $posting)
    {
        $redisPosting = Redis::get('posting-'.$posting->id);

        if ($redisPosting) {
            return $redisPosting;
        }

        // Get posting with all relations
        $posting->load([
            'address',
            'address.model',
            'address.model.address',
            'address.model.client',
            'placements',
            'placements.workplace',
            'placements.placementType',
            'placements.employee',
            'placements.employee.avatar',
            'commitments',
            'regions',
        ]);

        Redis::set('posting-'.$posting->id, $posting, 'EX', 300);

        $posting->placements = $posting->placements->filter(function ($placement) use ($agency) {
            return is_null($placement->employee_id) || $placement->employee->agency_id == $agency->id;
        });

        $posting->commitments = $posting->commitments->filter(function ($commitment) use ($agency) {
            return $commitment->agency_id == $agency->id;
        });

        return $posting;
    }
}
