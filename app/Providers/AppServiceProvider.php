<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Sync\QueueTracker\QueueTracker;
use App\Services\Sync\QueueTracker\AgencyQueueTracker;
use App\Services\Sync\QueueTracker\QueueTrackerInterface;

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
