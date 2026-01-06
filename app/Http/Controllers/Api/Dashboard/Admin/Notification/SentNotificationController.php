<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Notification;

use App\Models\User;
use App\Enums\UserType;
use App\Filter\UserFilter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ManagementNotificationJob;
use Modules\Notification\Models\NotificationGroup;
use App\Http\Requests\Api\Dashboard\Admin\Notification\NotificationRequest;
use App\Http\Resources\Api\Dashboard\Admin\Notification\{SentNotificationResource, UserResource, ShowNotificationResource};

class SentNotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationGroup::query()
            ->with('notifications')
            ->orderByDesc('created_at')
            ->paginate(10);
        SentNotificationResource::wrap('notifications');
        return json(SentNotificationResource::collection($notifications), status: 'success', headerStatus: 200);
    }

    public function show($id)
    {
        $notification = NotificationGroup::findOrFail($id);
        return json(ShowNotificationResource::make($notification), status: 'success', headerStatus: 200);
    }

    public function store(NotificationRequest $request)
    {
        $data = [
            'sender_data' => config('app.name'),
            'notify_type' => 'management',
            'title' => $request->title,
            'body' =>   $request->body
        ];


        $group = NotificationGroup::create([
            'data' => $data,
            'type' => $request->type,
            'channel' => $request->channel,
            'is_all' => $request->user_ids == 'all'
        ]);

        $requestData = [
            'data' => $data,
            'channel' => $request->channel,
            'type' => $request->type,
            'user_ids' => $request->user_ids,
        ];

        ManagementNotificationJob::dispatch($data, $requestData, $group);
        return response()->json(['status' => 'success', 'data' => null, 'messages' => trans('Sent successfully')]);
    }

    public function destroy($id)
    {
        $notification = NotificationGroup::findOrFail($id);
        $notification->delete();
        return json(null,trans('Deleted successfully'),'success',200);
    }

    public function users(UserFilter $filter)
    {
        $users = User::active()->whereIn('user_type', [UserType::VENDOR,UserType::CLIENT])
            ->filter($filter)->get();
        return json(UserResource::collection($users), status: 'success', headerStatus: 200);
    }
}
