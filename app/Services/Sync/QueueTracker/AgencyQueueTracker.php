<?php

namespace App\Services\Sync\QueueTracker;

use Illuminate\Support\Facades\Redis;

class AgencyQueueTracker implements QueueTrackerInterface
{
    public static function setJob($job)
    {
        if (self::checkIfJobInQueue($job)) {
            return true;
        } else {
            $key = self::getKey($job);
            Redis::set('agencies_queue.'.$key, true, 'EX', 600);
            dispatch($job);

            return true;
        }

        return false;
    }

    public static function unsetJob($job)
    {
        if (self::checkIfJobInQueue($job)) {
            $key = self::getKey($job);
            Redis::del('agencies_queue.'.$key);
        }
    }

    public static function getKey($job)
    {
        if (get_class($job) == 'Illuminate\Queue\Jobs\DatabaseJob') {
            $payload = json_decode($job->getRawBody());
            $data = unserialize($payload->data->command);

            return $data->key;
        }

        return $job->key;
    }

    public static function checkIfJobInQueue($job)
    {
        $key = AgencyQueueTracker::getKey($job);

        if ($key) {
            return boolval(Redis::get('agencies_queue.'.$key));
        }

        return false;
    }
}
