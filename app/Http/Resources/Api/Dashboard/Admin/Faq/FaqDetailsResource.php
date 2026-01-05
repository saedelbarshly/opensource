<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locales = [];
        foreach (config('translatable.locales') as $local) {
            $locales[$local]['question'] = isset($this->translate($local)?->question) ? $this->translate($local)?->question : null;
            $locales[$local]['answer'] = isset($this->translate($local)?->answer) ? $this->translate($local)?->answer : null;
        }
        return [
                'id'        => (int)$this->id,
                'ordering'  => (int)$this->ordering,
                'is_active' => (bool)$this->is_active,
            ] + $locales;
    }
}
