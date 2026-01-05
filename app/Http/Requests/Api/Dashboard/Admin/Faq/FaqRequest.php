<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Faq;

use App\Http\Requests\ApiMasterRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'ordering' => [
                'required',
                Rule::unique('faqs', 'ordering')
                    ->ignore($this->faq)
            ],
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.question'] = 'required|string';
            $rules[$locale . '.answer'] = 'required|string';
        }
        return $rules;
    }

    public function messages()
    {
        $messages = [];
        foreach (config('translatable.locales') as $locale) {
            $messages[$locale . '.question.required'] = trans("question {$locale} is required");
            $messages[$locale . '.answer.required'] = trans("answer {$locale} is required");
        }
        return $messages;
    }
}
