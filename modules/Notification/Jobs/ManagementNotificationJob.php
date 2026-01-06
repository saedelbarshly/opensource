<?php

namespace App\Jobs;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Notification\Models\NotificationGroup;
use Modules\Notification\Jobs\ChunkNotificationJob;

class ManagementNotificationJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600;
    private const CHUNK_SIZE = 1000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $data,
        private array $requestData,
        private ?NotificationGroup $group = null,
    )
    {}


    /**
     * Execute the job.
     */
    public function handle()
    {
        $data = [
            'title' => $this->requestData['data']['title'],
            'body' =>  $this->requestData['data']['body'],
            'notify_type' => $this->data['notify_type'],
            'sender_data' => $this->data['sender_data'],
            'notify_id' => null,
        ];

        $userIds = $this->requestData['user_ids'] ??  [];
        $type = $this->requestData['type'] ?? null;
        $channel = $this->requestData['channel'] ?? null;

        // Build base query
        $query = User::query()
            ->where('user_type', '!=', UserType::ADMIN)
            ->where('is_active', true);


        if (is_array($userIds) && count($userIds) > 0) {
            $query->whereIn('id', $userIds);
        }else{
            $userTypes = match ($type) {
                'all'     => [
                    UserType::CLIENT,
                    UserType::VENDOR,
                ],
                'client'  => [UserType::CLIENT],
                'vendor' => [UserType::VENDOR],
                default   => [],
            };

            if (!empty($userTypes)) {
                $query->whereIn('user_type', $userTypes);
            }
        }

        $query->chunk(self::CHUNK_SIZE, function ($usersChunk) use ($data) {
            $userIds = $usersChunk->pluck('id')->toArray();
            ChunkNotificationJob::dispatch($userIds, $data, $this->group);
        });
    }
}
