<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Http\Requests\ApiMasterRequest;

class RefreshTokenRequest extends ApiMasterRequest
{
    public function rules(): array
    {
        return [
            'old_device_token' => 'sometimes|nullable|string',
            'device_token'     => 'required|string',
            'device_type'      => 'required|string|in:android,ios'
        ];
    }

    public function attributes(): array
    {
        return [
            'old_device_token' => __('Old device token'),
            'device_token'     => __('new device token'),
            'device_type'      => __('device type')
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
