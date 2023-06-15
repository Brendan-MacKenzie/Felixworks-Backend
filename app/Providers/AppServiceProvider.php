<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QueueTracker\QueueTracker;
use App\Services\QueueTracker\AgencyQueueTracker;
use App\Services\QueueTracker\QueueTrackerInterface;

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
