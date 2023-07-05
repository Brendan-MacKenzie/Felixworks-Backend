<?php

namespace App\Helpers;

use App\Models\Posting;
use Illuminate\Support\Facades\Redis;

trait RedisHelper
{
    public function syncRedisPosting(Posting $posting, string $action = 'created', bool $withLoad = true)
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

        if (in_array($action, ['created', 'updated', 'read'])) {
            Redis::set('posting-'.$posting->id, json_encode($posting), 'EX', config('app.REDIS_POSTING_EXPIRATION'));
        }

        if (in_array($action, ['deleted'])) {
            Redis::del('posting-'.$posting->id);
        }
    }

    public static function staticSyncRedisPosting(Posting $posting, string $action = 'created', bool $withLoad = true)
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

        if (in_array($action, ['created', 'updated', 'read'])) {
            Redis::set('posting-'.$posting->id, json_encode($posting), 'EX', config('app.REDIS_POSTING_EXPIRATION'));
        }

        if (in_array($action, ['deleted'])) {
            Redis::del('posting-'.$posting->id);
        }
    }
}
