<?php

namespace App\Http\Requests\Api\General\User;

use App\Enums\UserType;
use Illuminate\Validation\Rule;
use App\Http\Requests\ApiMasterRequest;

class UserRequest extends ApiMasterRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->getUserId();
        $isUpdate = !is_null($userId);

        return array_merge(
            $this->baseRules($userId, $isUpdate),
            $this->rulesByType($this->input('user_type'))
        );
    }

    protected function baseRules(?int $userId, bool $isUpdate): array
    {
        return [
            'user_type'   => ['required', Rule::in(UserType::values())],

            'name'        => ['required', 'string', 'max:255'],

            'email'       => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'phone'       => [
                'required',
                'string',
                Rule::unique('users', 'phone')->ignore($userId),
            ],

            'phone_code'  => ['required', 'string'],

            'password'    => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'confirmed',
            ],

            'avatar'      => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function rulesByType(string $userType): array
    {
        return match ($userType) {
            UserType::VENDOR->value => $this->vendorRules(),
            UserType::ADMIN->value  => $this->adminRules(),
            default                 => [],
        };
    }

    protected function vendorRules(): array
    {
        return [
            'logo'             => ['nullable', 'image', 'max:2048'],
            'description'      => ['nullable', 'string'],

            'category_id'      => ['nullable', 'exists:categories,id'],
            'country_id'       => ['nullable', 'exists:countries,id'],
            'city_id'          => ['nullable', 'exists:cities,id'],

            'lat'              => ['nullable', 'numeric'],
            'lng'              => ['nullable', 'numeric'],

            'property_number'  => ['nullable', 'string'],
            'details'          => ['nullable', 'string'],
            'url'              => ['nullable', 'string'],

            'is_default'       => ['nullable', 'boolean'],


            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:categories,id',
        ];
    }

    protected function adminRules(): array
    {
        return [
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    protected function getUserId(): ?int
    {
        return $this->route('user')?->id
            ?? $this->input('id');
    }
}
