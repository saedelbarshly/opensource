<?php

namespace App\Http\Resources\Api\App\Notification;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseData = [
            'id'            => (string) $this->id,
            'icon'          => $this->data['icon'] ?? null,
            'notifiable_id' => $this->data['notifiable_id'] ?? null,
            'created_time'  => $this->created_at->diffForHumans(),
            'created_at'    => date('d/m/Y', strtotime($this->created_at)),
            'read_at'       => Carbon::parse($this->read_at)->format('Y-m-d H:i A:s'),
            'is_read'       => is_null($this->read_at),
        ];

        // Handle different notification types
        if (isset($this->data['notify_type']) && $this->data['notify_type'] === 'management') {
            // Get current locale
            $locale = app()->getLocale();

            return array_merge($baseData, [
                'title'     => $this->data['title'][$locale] ?? $this->data['title']['en'],
                'body'      => $this->data['body'][$locale] ?? $this->data['body']['en'],
                'type'      => 'management',
                'sender_by' => $this->data['sender_by'] ?? null
            ]);
        }

        return array_merge($baseData, [
            'title' => __($this->data['title']),
            'body'  => __($this->data['body'],$this->data['params'] ?? []),
            'type'  => $this->data['type'] ?? 'order'
        ]);
    }
}
