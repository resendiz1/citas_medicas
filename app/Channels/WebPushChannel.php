<?php

namespace App\Channels;

use App\Actions\SendPushNotification;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toWebPush')) return;

        $data = $notification->toWebPush($notifiable);

        SendPushNotification::send(
            $notifiable->id,
            $data['title'] ?? 'Citas Médicas',
            $data['body'] ?? '',
            $data['data'] ?? []
        );
    }
}
