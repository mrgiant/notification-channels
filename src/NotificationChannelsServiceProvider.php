<?php

namespace Mrgiant\NotificationChannels;

use Illuminate\Support\ServiceProvider;

class NotificationChannelsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //  Register services 
    }

    public function boot()
    {

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notification-channels');

       
    }
}