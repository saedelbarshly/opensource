<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Country;

use App\Http\Requests\ApiMasterRequest;
use App\Models\Country;
use Illuminate\Validation\Rule;

class CountryRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $country = isset($this->country) ? Country::findOrFail($this->country) : null;
        $status = isset($this->country) ? 'sometimes' : 'required';
        $rules = [
            'code'                  => ['required', 'string', 'between:2,3', Rule::unique('countries')->ignore($country)->where(function ($query) {
                return $query->whereNull('deleted_at');
            })],
            'phone_code'            => ['required', 'numeric', 'digits_between:1,3', Rule::unique('countries')->ignore($country)->where(function ($query) {
                return $query->whereNull('deleted_at');
            })],
            'phone_length'          => 'required|numeric',
            'national_id_length'    => 'required|numeric',
            'continent'             => 'nullable|in:africa,europe,asia,south_america,north_america,australia',
            'media.flag'            =>  $status . '|string|exists:media,id',
            'is_active'             => 'nullable|boolean',
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.nationality'] = 'nullable|string|between:3,100000';
            $rules[$locale . '.currency']    = 'nullable|string|between:3,100000';
            $rules[$locale . '.slug']        = 'nullable|string|between:2,100000';
            $rules[$locale . '.short_name']  = 'nullable|string|between:3,100000';
            $rules[$locale . '.name']        = [
                'required',
                'between:3,100000',
                Rule::unique('country_translations', 'name')->where(function ($query) use ($locale) {
                    return $query->where('locale', $locale)->where('country_id', '!=', $this->country);
                })
            ];
        }
        return $rules;
    }

}
