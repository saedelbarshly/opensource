<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Brand;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $locales = [];

        foreach (config('translatable.locales') as $locale) {
            $locales[$locale]['name'] = $this->translate($locale)?->name;
        }

        return [
                'id'        => (int) $this->id,
                'is_active' => (bool) $this->is_active,
                'image'     => (object) $this->image_object,
            ] + $locales;
    }
}
