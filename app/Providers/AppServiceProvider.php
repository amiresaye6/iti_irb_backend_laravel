<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Observers\NotificationObserver;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        //
    }

   
    public function boot(): void
    {
        Notification::observe(NotificationObserver::class);
    }
}
