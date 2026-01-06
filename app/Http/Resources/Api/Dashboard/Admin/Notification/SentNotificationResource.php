<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SentNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        return [
            'id'            => @$this?->id,
            'channel'       => @$this?->channel,
            'type'          => @$this?->type,
            'title'         => @$this?->data['title'][$locale] ?: null,
            'body'          => @$this?->data['body'][$locale] ?: null,
            'users_count'   => @$this?->user_count,
            'read_count'    => @$this?->notifications()->where('read_at', '!=', null)->count(),
            'creation_date' => @$this?->created_at?->format('Y-m-d'),
            // 'published_at'  => @$this?->release_at?->translatedFormat('F d, Y H:i A')
        ];
    }
}
