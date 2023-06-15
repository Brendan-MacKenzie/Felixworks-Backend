<?php

namespace App\Providers;

use App\Sync\QueueTracker\QueueTracker;
use Illuminate\Support\ServiceProvider;
use App\Sync\QueueTracker\AgencyQueueTracker;
use App\Sync\QueueTracker\QueueTrackerInterface;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        QueueTrackerInterface::class => AgencyQueueTracker::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        QueueTracker::setQueueSettings();
    }
}
