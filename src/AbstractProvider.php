<?php

namespace Mrgiant\NotificationChannels;

use App\Models\NotificationChannel;

abstract class AbstractProvider implements NotificationChannelInterface
{
    protected object $notificationChannel;

    public function __construct(object $notificationChannel)
    {
        $this->notificationChannel = $notificationChannel;
    }
}
