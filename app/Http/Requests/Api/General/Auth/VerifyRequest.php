<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Enums\UserType;
use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rule;

class VerifyRequest extends ApiMasterRequest
{
    protected function prepareForValidation(): void
    {
        $userType = $this->header('user_type');

        $authType = UserType::authType($userType);

        $this->merge([
            'user_type' => $userType,
            'auth_type' => $authType,
        ]);
    }

    public function rules(): array
    {
        return  [
            'auth'          => [
                Rule::exists('users', $this->auth_type)
                    ->whereNull('deleted_at')
                    ->where('user_type', $this->user_type)
            ],
            'phone_code'    => [
                "required_if:auth_type,phone,exists:countries,phone_code",
            ],
            'code'          => [
                'required',
                'digits_between:4,6',
                Rule::exists('users', 'reset_code')
                    ->where('user_type', $this->user_type)
                    ->whereNull('deleted_at')
                    ->when($this->auth_type == 'phone' ,  fn($q) => $q->where('phone_code', $this->phone_code))
                    ->where($this->auth_type, $this->auth)
            ],
            'device_token'  => 'sometimes|string',
            'device_type'   => 'sometimes|string|in:android,ios,huawei,web',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_code'    => __('Phone code'),
            'auth_type'     => __('Auth type'),
            'auth'          => __($this->auth_type === 'email' ? 'Email' : 'Phone'),
            'device_token'  => __('Device token'),
            'device_type'   => __('Device type'),
            'code'          => __('OTP Code')
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}