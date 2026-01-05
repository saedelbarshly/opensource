<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Enums\UserType;
use Illuminate\Validation\Rule;
use App\Http\Requests\ApiMasterRequest;

class ForgetPasswordRequest extends ApiMasterRequest
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
            'auth'       => [
                'required',
                Rule::exists('users', $this->auth_type)
                    ->whereNull('deleted_at')
                    ->where('user_type', $this->user_type)
            ],
            'phone_code' => [
                'required_if:auth_type,phone',
                'exists:countries,phone_code',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'auth_type'  => __('Auth Type'),
            'auth'       => __($this->auth_type === 'email' ? 'Email' : 'Phone'),
            'phone_code' => __('Phone Code'),
            'user_type'  => __('User Type'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}