<?php

namespace Modules\Notification\Models;

use App\Enums\ChannelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationGroup extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'channel' => ChannelType::class,
        'data' => 'array',
        'release_at' => 'datetime'
    ];

    protected $with = ['notifications'];
    protected $appends = ['user_count'];

    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class, 'notification_group_id');
    }

    public function getUserCountAttribute()
    {
        return $this->notifications
            ? $this->notifications->pluck('notifiable_id')->unique()->count()
            : 0;
    }
}
