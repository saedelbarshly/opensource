<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Http\Requests\ApiMasterRequest;

class UpdateProfileRequest extends ApiMasterRequest
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
        return [
            'name' => 'sometimes|nullable|string',
            'avatar'    => ['sometimes', 'nullable', 'string', 'exists:media,id', 'exists:media,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => __('Name'),
            'avatar'     => __('Avatar'),
        ];
    }
}