<?php

namespace App\Http\Requests\Api\Dashboard\Admin\City;

use App\Http\Requests\ApiMasterRequest;
use App\Models\City;
use Illuminate\Validation\Rule;

class CityRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $status = isset($this->city) ? City::findOrfail($this->city) : null;
        $rules = [
            'country_id'    => 'required|exists:countries,id',
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
            'location'      => 'required|string|max:255',
            'code'          => 'sometimes|string|max:30',
            'postal_code'   => 'sometimes|string|max:30',
            'is_active'     => 'required|in:0,1',
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name']       = [
                'required',
                'between:3,100000',
                Rule::unique('city_translations', 'name')->where(function ($query) use ($locale) {
                    return $query->where('locale', $locale)->where('city_id', '!=', $this->city);
                })
            ];
        }

        return $rules;
    }
}
