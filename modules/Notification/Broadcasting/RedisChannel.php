<?php

namespace Modules\Notification\Broadcasting;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisChannel
{
    public function send($notifiable, Notification $notification): void
    {
        try {
            $payload = [
                'event' => "notification:" . $notifiable->id,
                'data'  => $notification->data ?? []
            ];

            Redis::publish("laravel-channel", json_encode($payload));

        } catch (\Throwable $e) {
            Log::error('Redis Channel Error', [
                'notifiable_id' => $notifiable->id ?? null,
                'notification' => get_class($notification),
                'error' => $e->getMessage()
            ]);
        }
    }
}
