<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Enums\UserType;
use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends ApiMasterRequest
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
                "required_if:auth_type,phone,exists:countries,phone_code",
            ],
            'code'       => [
                'required',
                'digits_between:4,6',
                Rule::exists('users', 'reset_code')
                    ->where('user_type', $this->user_type)
                    ->whereNull('deleted_at')
                    ->when($this->auth_type == 'phone' ,  fn($q) => $q->where('phone_code', $this->phone_code))
                    ->where($this->auth_type, $this->auth)
            ],
            'password'   => ['required', 'string', Password::min(8)->max(16)],
        ];
    }

    public function attributes(): array
    {
        return [
            'auth_type'  => __('Auth type'),
            'auth'       => __($this->auth_type === 'email' ? 'Email' : 'Phone'),
            'phone_code' => __('Phone Code'),
            'password'   => __('Password'),

        ];
    }

    public function messages(): array
    {
        return [
            'password.min'              => __('The password must be at least 8 characters.'),
            'password.mixed'            => __('The password must contain at least one uppercase letter and one lowercase letter.'),
            'password.symbols'          => __('The password must contain at least one special character.'),
        ];
    }

}