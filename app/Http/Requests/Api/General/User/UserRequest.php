<?php

namespace App\Http\Requests\Api\General\User;

use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rule;

class UserRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->id;

        $passwordRule = isset($userId) ? 'nullable' : 'required';

        return [
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone'     => ['required', 'string', Rule::unique('users', 'phone')->ignore($userId)],
            'phone_code' => 'required|string',
            'password'  => $passwordRule . '|string|min:6|confirmed',
            'avatar'    => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];
    }
}