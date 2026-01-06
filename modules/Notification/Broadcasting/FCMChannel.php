<?php

namespace Modules\Notification\Broadcasting;


use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Modules\Notification\Services\FcmClient;
use Modules\Notification\Broadcasting\Payloads\IosPayload;
use Modules\Notification\Broadcasting\Payloads\AndroidPayload;

class FCMChannel
{
    public function __construct(
        protected FcmClient $fcmClient
    ) {}
    public function send($notifiable, Notification $notification): void
    {
        try {
            $deviceTokens = $notifiable->routeNotificationForFcm();

            if (empty($deviceTokens)) {
                return;
            }

            $data = $this->transformData($notifiable, $notification);

            foreach ($deviceTokens as $platform => $token) {
                try {
                    $payload = match ($platform) {
                        'android' => AndroidPayload::build($token, $data),
                        'ios'     => IosPayload::build($token, $data),
                        default   => null,
                    };

                    if ($payload) {
                        $this->fcmClient->send($payload);
                    }
                } catch (\Throwable $e) {
                    Log::error('FCM Send Error', [
                        'platform' => $platform,
                        'notifiable_id' => $notifiable->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM Channel Error', [
                'notifiable_id' => $notifiable->id ?? null,
                'notification' => get_class($notification),
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function transformData($notifiable, Notification $notification): array
    {
        $data = $notification->data ?? [];
        $notifyType = $data['notify_type'] ?? null;

        if ($notifyType === 'management') {
            return $this->transformManagementData($notifiable, $data);
        }

        return $this->transformGeneralData($notifiable, $data);
    }

    protected function transformManagementData($notifiable, array $data): array
    {
        $locale = $this->resolveLocale($notifiable);

        return [
            'notify_type' => 'management',
            'notify_id'   => null,
            'title'       => $data['title'][$locale] ?? $data['title']['en'] ?? null,
            'body'        => $data['body'][$locale] ?? $data['body']['en'] ?? null,
            'sender_data' => $data['sender_data'] ?? null,
        ];
    }

    protected function transformGeneralData($notifiable, array $data): array
    {
        $locale = $notifiable->locale ?? 'en';
        $params = $data['params'] ?? [];

        $titleTemplate = trans($data['title'] ?? '', [], $locale);
        $bodyTemplate = trans($data['body'] ?? '', [], $locale);

        return [
            'title'         => $this->applyParams($titleTemplate, $params),
            'body'          => $this->applyParams($bodyTemplate, $params),
            'notify_type'   => $data['notify_type'] ?? null,
            'icon'          => $data['icon'] ?? null,
            'notify_id'     => $data['notify_id'] ?? null,
        ];
    }

    protected function applyParams(string $text, array $params): string
    {
        $normalizedParams = array_change_key_case($params, CASE_LOWER);

        return preg_replace_callback('/:([a-zA-Z0-9_]+)/', function ($matches) use ($normalizedParams) {
            $key = strtolower($matches[1]);
            return $normalizedParams[$key] ?? $matches[0];
        }, $text);
    }

    protected function resolveLocale($notifiable): string
    {
        $locales = config('translatable.locales', ['en']);
        $userLocale = $notifiable->locale ?? 'en';

        return in_array($userLocale, $locales) ? $userLocale : 'en';
    }
}
