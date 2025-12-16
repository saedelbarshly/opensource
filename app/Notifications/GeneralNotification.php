<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Notification\Traits\NotificationChannelResolver;

class GeneralNotification extends Notification
{
    use Queueable,NotificationChannelResolver;
    public $data;
    public ?string $sendVia;


    /**
     * Create a new notification instance.
     */
    public function __construct(array $data, ?string $via = null)
    {
        $this->data = $data;
        $this->sendVia = $via;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (!$notifiable) {
            return [];
        }

        if ($this->sendVia === 'mail') {
            return ['mail'];
        }
        return $this->resolveChannels($notifiable);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? 'en';

        return (new MailMessage)
            ->subject(trans($this->data['title'] ?? 'Notification', [], $locale))
            ->line(trans($this->data['body'] ?? '', [], $locale));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
