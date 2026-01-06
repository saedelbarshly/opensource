<?php

namespace App\Http\Resources\Api\Dashboard\Admin\City;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'is_active'     => (bool) $this->is_active,
            'name'          => $this->name,
        ];
    }
}
