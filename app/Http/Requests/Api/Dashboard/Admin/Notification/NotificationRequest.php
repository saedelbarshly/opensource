<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Notification;

use App\Enums\ChannelType;
use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rule;

class NotificationRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'channel' => ['required','string',Rule::in(ChannelType::values())],
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules['title.' . $locale] = 'required|string|between:3,200';
            $rules['body.' . $locale]  = 'required|string|between:3,10000';
        }

        return [
                'type'       => 'required|in:all,admin,company,driver,client,specific',
                'user_ids'   => 'sometimes|array',
                'user_ids.*' => 'sometimes|exists:users,id',
            ] + $rules;
    }
}
