<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Enums\UserType;
use App\Http\Requests\ApiMasterRequest;
use App\Models\Country;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends ApiMasterRequest
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
        $country = Country::where('phone_code', $this->phone_code)->first();

        return [
            'name'          => ['sometimes', 'nullable', 'string', 'min:2', 'max:50'],
            'avatar'        => ['sometimes', 'nullable', 'string', 'exists:media,id'],
            'email'         => [
                'required_if:auth_type,email',
                Rule::unique('users', 'email')
                    ->where('user_type', $this->user_type)
                    ->where('is_active', 1)
                    ->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($country) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail(__('The :attribute must be a valid email address.'));
                    }
                }
            ],
            'phone'         => [
                'required_if:auth_type,phone',
                Rule::unique('users', 'phone')
                    ->where('user_type', $this->user_type)
                    ->where('is_active', 1)
                    ->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($country) {

                    if (!$country) {
                        $fail(__('Invalid country code'));
                    }
                    if (strlen($value) != $country->phone_length) {
                        $fail(__('Phone number must be :digits digits', ['digits' => $country->phone_length]));
                    }
                }
            ],
            'phone_code'    => [
                "required_if:auth_type,phone",
                "exists:countries,phone_code,deleted_at,NULL",
            ],
            'password'      => [
                'required',
                'string',
                Password::min(8)->max(16)
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'          => __('Name'),
            'phone'         => __('Phone'),
            'email'         => __('Email'),
            'password'      => __('Password'),
            'phone_code'    => __('Phone Code'),
            'avatar'        => __('Avatar'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}