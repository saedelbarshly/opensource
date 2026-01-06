<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Brand;

use App\Http\Requests\ApiMasterRequest;

class BrandRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $status = isset($this->brand) ? 'nullable' : 'required';

        $rules = [
            'image' => $status . '|string',
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = $status . '|string|between:2,100';
        }

        return $rules;
    }
}
