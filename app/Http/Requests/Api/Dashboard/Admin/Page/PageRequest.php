<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Page;

use App\Enums\PageType;
use Illuminate\Validation\Rule;

use App\Http\Requests\ApiMasterRequest;

class PageRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'ordering' => [
                'nullable',
                Rule::unique('pages', 'ordering')
                    ->ignore($this->page)
                    ->where(function ($query) {
                        return $query->where('type', $this->input('type'));
                    }),
            ],

            'type'      => ['required', Rule::in(PageType::values())],
            'gallery'   => ['sometimes', 'array'],
            'gallery.*' => ['string', 'exists:media,id'],
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.title']    = 'required';
            $rules[$locale . '.content']  = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        foreach (config('translatable.locales') as $locale) {
            $messages[$locale . '.title.required']    = trans("title {$locale} is required");
            $messages[$locale . '.content.required']  = trans("content {$locale} is required");
        }
        return $messages;
    }
}
