<?php

namespace App\Http\Resources\Api\Dashboard\Admin\City;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityDetailsResource extends JsonResource
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
            $locales[$locale]['name']       = @$this->translate($locale)?->name;
        }

        return [
                'id'            => $this->id,
                'is_active'     => (bool) $this->is_active,
                'lat'           => $this->lat,
                'lng'           => $this->lng,
                'location'      => $this->location,
                'code'          => $this->code,
                'postal_code'   => $this->postal_code,
                'country'       => [
                    'id'        => $this->country->id,
                    'name'      => $this->country->name
                ],
            ]+ $locales;
    }
}
