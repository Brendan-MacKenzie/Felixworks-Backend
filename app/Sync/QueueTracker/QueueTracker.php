<?php

namespace App\Sync\QueueTracker;

use Illuminate\Support\Facades\Queue;

class QueueTracker
{
    public static function setQueueSettings()
    {
        // Settings after queue
        Queue::after(function ($event) {
            switch($event->job->getQueue()) {
                case 'agencies':
                    AgencyQueueTracker::unsetJob($event->job);
                    break;
                default:
                    break;
            }
        });

        // Settings failing queue
        Queue::failing(function ($event) {
            switch($event->job->getQueue()) {
                case 'agencies':
                    AgencyQueueTracker::unsetJob($event->job);
                    break;
                default:
                    break;
            }
        });
    }
}
