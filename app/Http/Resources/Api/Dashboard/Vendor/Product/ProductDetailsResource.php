<?php

namespace App\Http\Resources\Api\Dashboard\Vendor\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
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
            $locales[$locale]['name']       = $this->translate($locale)?->name;
        }
        return [
                'id'            => $this->id,
                'image'         => $this->image,
                'is_active'     => $this->is_active,
                'is_returnable' => $this->is_returnable,
                'is_taxable'    => $this->is_taxable,
        ] + $locales;
    }
}
