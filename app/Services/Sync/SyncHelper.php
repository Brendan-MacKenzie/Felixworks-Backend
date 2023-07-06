<?php

namespace App\Services\Sync;

use App\Models\Agency;
use App\Models\Posting;
use App\Jobs\AgencyActionJob;
use App\Services\Sync\QueueTracker\AgencyQueueTracker;

trait SyncHelper
{
    public static function sync(Agency $agency, mixed $subject, string $type, mixed $data, bool $withCache = true, ?int $uniqueId = null)
    {
        $job = new AgencyActionJob($agency, $type, $data, $uniqueId);
        AgencyQueueTracker::setJob($job);

        if ($withCache) {
            if ($agency && $subject instanceof Posting) {
                RedisHelper::syncPosting($subject);
            }
        }
    }

    public static function syncInBulk($agencies, mixed $subject, string $type, mixed $data, ?int $uniqueId = null)
    {
        foreach ($agencies as $agency) {
            self::sync($agency, $subject, $type, $data, false, $uniqueId);
        }

        if (count($agencies) > 0 && $subject instanceof Posting) {
            RedisHelper::syncPosting($subject);
        }
    }

    public static function syncPostings($postings, string $type)
    {
        foreach ($postings as $posting) {
            self::syncInBulk($posting->agencies, $posting, $type, $posting->id);
        }
    }
}
