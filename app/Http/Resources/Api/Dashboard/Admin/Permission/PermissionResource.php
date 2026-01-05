<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'front_route_name'  => $this->front_route_name,
            'back_route_name'   => $this->back_route_name,
        ];
    }
}
