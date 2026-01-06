<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;


class ShowNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $locale = app()->getLocale();
        $notifications = @$this?->notifications()->with('notifiable')->get();

        $users = $notifications?->map(function ($notification) {
            $user = $notification?->notifiable;
            if ($user) {
                return [
                    'id'        => $user?->id,
                    'name' => $user?->name ?? $user->name,
                    'is_read'   => $notification?->read_at !== null,
                ];
            }
            return null;
        })->filter()->unique('id')->values();

        $page = request()->get('page', 1);
        $perPage = 25;
        $paginated = new LengthAwarePaginator(
            $users->forPage($page, $perPage),
            $users->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'id'          => @$this?->id,
            'channel'       => @$this?->channel,
            'type'          => @$this?->type,
            'title'       => $this->data['title'][$locale] ?? $this->data['title']['en'] ?? null,
            'body'        => $this->data['body'][$locale] ?? $this->data['body']['en'] ?? null,

            // Other static fields
            'sender_data' => $this->data['sender_data'] ?? null,
            'notify_type' => $this->data['notify_type'] ?? null,
            'users_count' => $this?->user_count,
            'users'       => $paginated,
        ];
    }
}
