<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Country;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locales = [];

        foreach (config('translatable.locales') as $locale) {
            $locales[$locale]['name']        = @$this->translate($locale)?->name;
            $locales[$locale]['short_name']  = @$this->translate($locale)?->short_name;
            $locales[$locale]['currency']    = @$this->translate($locale)?->currency;
            $locales[$locale]['nationality'] = @$this->translate($locale)?->nationality;
        }

        return [
                'id'                 => $this->id,
                'is_active'          => (bool) $this->is_active,
                'code'               => $this->code,
                'phone_code'         => $this->phone_code,
                'phone_length'       => $this->phone_length,
                'national_id_length' => $this->national_id_length,
                'continent'          => $this->continent,
                'media'              => $this->media,
            ] + $locales;
    }
}
