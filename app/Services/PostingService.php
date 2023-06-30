<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Posting;
use App\Helpers\RedisHelper;
use Illuminate\Support\Facades\Redis;

class PostingService extends Service
{
    use RedisHelper;

    public function store(array $data)
    {
    }

    public function update(array $data, mixed $placement)
    {
    }

    public function delete(mixed $placement)
    {
    }

    public function get(mixed $placement)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
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

        $this->syncRedisPosting($posting, 'read', false);

        $posting->placements = $posting->placements->filter(function ($placement) use ($agency) {
            return is_null($placement->employee_id) || $placement->employee->agency_id == $agency->id;
        });

        $posting->commitments = $posting->commitments->filter(function ($commitment) use ($agency) {
            return $commitment->agency_id == $agency->id;
        });

        return $posting;
    }
}
