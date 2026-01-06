<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Category;

use App\Http\Requests\ApiMasterRequest;

class CategoryRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $status = isset($this->category) ? 'nullable' : 'required';
        $rules = [
            'level'         => 'nullable|numeric|in:1,2,3',
            'parent_id'     => 'required_if:level,2,3|numeric|exists:categories,id',
            'image'         => $status . '|string|exists:media,id',
            'is_active'     => 'required|boolean',
            'is_returnable' => 'required|boolean',
            'is_taxable'    => 'required|boolean',
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name']          = 'required|string|max:100';
        }
        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'image.required' => trans('Image is required'),
        ];
        foreach (config('translatable.locales') as $locale) {
            $messages[$locale . '.name.required']    = trans("name {$locale} is required");
        }
        return $messages;
    }
}
