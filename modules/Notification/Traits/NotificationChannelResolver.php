<?php

namespace Modules\Notification\Traits;

use App\Enums\UserType;
use Illuminate\Support\Facades\Log;

trait NotificationChannelResolver
{
    protected function resolveChannels(mixed $notifiable): array
    {
        $channels = ['database'];

        try {
            # Super admins and supervisors only get Redis
            if ($this->isOnlyRedisPrivilegedUser($notifiable)) {
                $channels[] = 'redis';
            }

            # Other users get Redis + optional FCM
            if ($this->shouldSendFcm($notifiable)) {
                $channels[] = 'fcm';
            }
        } catch (\Throwable $th) {
           Log::error('Notification channel resolver error: ' . $th->getMessage());
        }

        return $channels;
    }

    protected function isOnlyRedisPrivilegedUser($notifiable): bool
    {
        return in_array($notifiable->user_type, [
            // UserType::ADMIN,
            // UserType::SUPER_ADMIN,
            // UserType::SUPERVISOR,
            // UserType::COMPANY,
        ]);
    }

    protected function shouldSendFcm($notifiable): bool
    {
        $allowedTypes = [
            // UserType::CLIENT,
            // UserType::DRIVER,
            // UserType::GUEST,
        ];

        return $notifiable->allow_notification && in_array($notifiable->user_type, $allowedTypes);
    }
}
