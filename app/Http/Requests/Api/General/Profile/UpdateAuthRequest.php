<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Http\Requests\ApiMasterRequest;
use App\Models\Country;
use Illuminate\Validation\Rule;

class UpdateAuthRequest extends ApiMasterRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
                Rule::exists('auth_verifications', $authType)
                    ->where('user_id', auth('api')->id())
                    ->where('reset_code', $this->input('code')),
            ],
            'phone_code' => [
                "required_if:auth_type,phone",
                "exists:countries,phone_code",
            ],
            'code'       => [
                'required',
                'digits_between:4,6',
                Rule::exists('auth_verifications', 'reset_code')
                    ->where('user_id', auth('api')->id())
                    ->where($authType, $this->auth),
            ]
        ];
    }
}