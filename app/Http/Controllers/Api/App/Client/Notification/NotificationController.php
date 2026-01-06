<?php

namespace App\Http\Controllers\Api\App\Client\Notification;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\Notification\NotificationResource;
use App\Models\User;

class NotificationController extends Controller
{
    private $user;
    public function __construct()
    {
        $this->user = User::find(auth('api')->id());
    }
    public function index()
    {
        $notifications = $this->user->notifications()->when(request()->type != null, function ($query) {
            if (request()->type == 'read') {
                $query->where('read_at', '!=', null);
            } elseif (request()->type == 'unread') {
                $query->where('read_at', null);
            }
        })->latest()->paginate(20);


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
