<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Enums\UserType;
use Illuminate\Validation\Rule;
use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends ApiMasterRequest
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
        return [
            'auth'         => [
                'required',
                Rule::exists('users', $this->auth_type)
                    ->whereNull('deleted_at')
                    ->where('user_type', $this->user_type)
            ],
            'phone_code'   => [
                'required_if:auth_type,phone',
                'exists:countries,phone_code',
            ],
            'password'     => [
                'required',
                'string',
                Password::min(8)->max(16)
            ],
            'device_token' => 'sometimes|string',
            'device_type'  => 'sometimes|string|in:android,ios,huawei,web',
        ];
    }

    public function messages(): array
    {
        return parent::messages() + ['auth.exists' => __('account doest exists')];
    }

    public function attributes(): array
    {
        return [
            'phone_code'   => __('Phone Code'),
            'auth'         => __($this->auth_type === 'email' ? 'Email' : 'Phone'),
            'password'     => __('Password'),
            'device_token' => __('Device Token'),
            'device_type'  => __('Device Type'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}