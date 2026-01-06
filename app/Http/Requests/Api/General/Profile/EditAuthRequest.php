<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Models\Country;
use Illuminate\Validation\Rule;
use App\Http\Requests\ApiMasterRequest;

class EditAuthRequest extends ApiMasterRequest
{
    public function rules(): array
    {
        $authType = $this->input('auth_type');

        $user_type = request()->header('user_type');

        return [
            'auth_type'  => 'required|in:email,phone',
            'auth'       => [
                'required',
                $authType === 'email' ? 'email:dns,rfc' : 'numeric',

                Rule::unique('users', $authType)
                    ->where('user_type', $user_type)
                    ->whereNull('deleted_at')
                    ->ignore(auth('api')->id()),

                function ($attribute, $value, $fail) use ($authType) {
                    $currentUser = auth('api')->user();

                    if ($value == $currentUser?->$authType) {
                        $fail(__('The :attribute must be different from your current one.'));
                    }

                    if ($authType === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail(__('The :attribute must be a valid email address.'));
                    }

                    if ($authType === 'phone') {
                        $country = Country::where('phone_code', $this->input('phone_code'))->first();
                        if ($country && strlen($value) != $country->phone_length) {
                            $fail(__('Phone number must be :digits digits.', ['digits' => $country->phone_length]));
                        }
                    }
                }
            ],
            'phone_code' => [
                "required_if:auth_type,phone",
                "exists:countries,phone_code",
            ],
        ];
    }
    public function attributes(): array
    {
        return [
            'auth_type'  => __('Auth Type'),
            'auth'       => $this->auth_type == 'email' ? __('Email') : __('Phone'),
            'phone_code' => __('Phone Code'),
        ];
    }
    public function authorize(): bool
    {
        return true;
    }
}
