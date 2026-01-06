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
                'data'  => $this->dataTransformer($notifiable, $notification)
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

    public function dataTransformer($notifiable, $notification): array
    {
        $data = $notification->data;
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
