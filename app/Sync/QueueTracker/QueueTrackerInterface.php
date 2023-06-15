<?php

namespace App\Sync\QueueTracker;

interface QueueTrackerInterface
{
    public static function setJob($job);

    public static function unsetJob($job);

    public static function getKey($job);

    public static function checkIfJobInQueue($job);
}
