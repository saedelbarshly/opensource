<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Dashboard\Admin\Notification\NotificationResource;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $user;
    public function __construct()
    {
       $this->user = User::find(auth('api')->id());
    }
    public function index()
    {
        $type = request('type');

        $this->user->unreadNotifications()->update(['read_at' => now()]);
        $notifications = $this->user->notifications()
            ->when($type, function ($query) use ($type) {
                match ($type) {
                    'read'   => $query->whereNotNull('read_at'),
                    'unread' => $query->whereNull('read_at'),
                    default  => null,
                };
            })
            ->latest()
            ->paginate(20);

        NotificationResource::wrap('notifications');
        return json(
           NotificationResource::collection($notifications)->response()->getData(true),
            trans('Retrieved successfully'),
            status: 'success',
            headerStatus: 200
        );
    }

    public function unReadCount()
    {
        return json($this->user->notifications()->whereNull('read_at')->count(), 'success', 'success', 200);
    }

    public function show(Request $request)
    {
        $request->filled('id')
            ? optional($this->user->notifications()->find($request->id))->markAsRead()
            : $this->user->unreadNotifications->markAsRead();
        return json(null, trans('Marked as read'), 'success', 200);
    }

    public function destroy(Request $request)
    {
        $request->filled('id')
            ? optional($this->user->notifications()->find($request->id))->delete()
            : $this->user->notifications()->delete();
        return json(null, trans('Deleted successfully'), 'success', 200);
    }

}
