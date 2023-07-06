<?php

namespace App\Services\Sync;

use App\Models\Posting;
use Illuminate\Support\Facades\Redis;

trait RedisHelper
{
    public static function syncPosting(Posting $posting, bool $withLoad = true)
    {
        if ($withLoad) {
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
        }

        Redis::set('posting-'.$posting->id, json_encode($posting), 'EX', config('app.REDIS_POSTING_EXPIRATION'));
    }
}
