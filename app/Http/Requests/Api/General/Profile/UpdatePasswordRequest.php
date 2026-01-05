<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Http\Requests\ApiMasterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends ApiMasterRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            "current_password" => [
                "required",
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth('api')->user()->password)) {
                        $fail(__('current password incorrect'));
                    }
                }
            ],
            'password'         => ['required', 'string', 'confirmed', Password::min(8)->max(16)],
        ];

    }

    public function attributes(): array
    {
        return [
            'current_password'  => __('current password'),
            'password'          => __('new password'),
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('current password is required'),
            'password.min'              => __('The password must be at least 8 characters.'),
            'password.mixed'            => __('The password must contain at least one uppercase letter and one lowercase letter.'),
            'password.symbols'          => __('The password must contain at least one special character.'),
        ];
    }

}