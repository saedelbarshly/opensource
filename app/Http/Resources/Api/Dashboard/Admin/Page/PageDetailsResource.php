<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageDetailsResource extends JsonResource
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
            $locales[$locale]['title']      = $this->translate($locale)?->title;
            $locales[$locale]['content']    = $this->translate($locale)?->content;
        }

        return [
                'id'            => $this->id,
                'gallery'       => $this->gallery,
                'type'          => $this->type,
            ] + $locales;
    }
}
