<?php

namespace App\Http\Requests\Api\Dashboard\Vendor\Product;

use App\Http\Requests\ApiMasterRequest;

class ProductRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $status = isset($this->product) ? 'nullable' : 'required';
        $rules = [
            'main'          => 'required|numeric|exists:media,id',
            'is_active'     => 'required|boolean',
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name']          = 'required|string|max:100';
        }
        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'main.required' => trans('Image is required'),
        ];
        foreach (config('translatable.locales') as $locale) {
            $messages[$locale . '.name.required']    = trans("name {$locale} is required");
        }
        return $messages;
    }
}
