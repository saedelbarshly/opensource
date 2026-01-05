<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionDetailsResource extends JsonResource
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
        }
        return [
            'id'                => $this->id,
            'prefix'            => $this->prefix,
            'front_route_name'  => $this->front_route_name,
            'back_route_name'   => $this->back_route_name,
        ] + $locales;
    }
}
