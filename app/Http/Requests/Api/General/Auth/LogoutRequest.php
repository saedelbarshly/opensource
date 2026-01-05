<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Http\Requests\ApiMasterRequest;

class LogoutRequest extends ApiMasterRequest
{
    public function rules(): array
    {
        return [
            'device_token' => 'sometimes|string',
            'device_type'  => 'sometimes|string|in:android,ios,huawei,web',
        ];
    }

    public function attributes(): array
    {
        return [
            'device_token' => __('Device token'),
            'device_type'  => __('Device type')
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}