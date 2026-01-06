<?php

namespace App\Notifications;

use App\Enums\UserType;
use App\Enums\ChannelType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Notification\Models\NotificationGroup;

class ManagementNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public array $data;
    public ?NotificationGroup $group;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data, ?NotificationGroup $group = null)
    {
        $this->data = $data;
        $this->group = $group;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // If notifications are disabled, use only database
        if (!$notifiable->allow_notification) {
            return ['database'];
        }

        $channels = ['database'];
        if (!$this->group) {
            return $channels;
        }

        switch ($this->group->channel) {
            case ChannelType::ALL:
                $channels[] = 'mail';
                $channels[] = 'redis';
                if ($this->shouldUseFcm($notifiable)) {
                    $channels[] = 'fcm';
                }
                break;

            case ChannelType::EMAIL:
                $channels[] = 'mail';
                break;

            case ChannelType::NOTIFICATION:
                $channels[] = 'redis';
                if ($this->shouldUseFcm($notifiable)) {
                    $channels[] = 'fcm';
                }
                break;
        }
        return $channels;
    }

    private function shouldUseFcm(object $notifiable): bool
    {
        return in_array($notifiable->user_type, [
            UserType::GUEST,
            UserType::CLIENT,
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->data['title']['en'])
            ->view('mail.notification-mail', [
                'name' => $notifiable->name,
                'title' => $this->data['title'][$notifiable->locale],
                'body' => $this->data['body'][$notifiable->locale],
            ]);
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
