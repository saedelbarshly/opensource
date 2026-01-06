<?php

namespace Modules\Notification\Jobs;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ManagementNotification;
use Modules\Notification\Models\NotificationGroup;


class ChunkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $userIds,
        private array $data,
        private ?NotificationGroup $group = null,
    )
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereIn('id', $this->userIds)->get();
        Notification::send($users, new ManagementNotification($this->data, $this->group));
    }
}
