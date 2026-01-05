<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Role;

use App\Http\Resources\Api\Dashboard\Admin\Permission\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleDetailsResource extends JsonResource
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
            'id'          =>  $this->id,
            'prefix'      =>  $this->prefix,
            'company'     =>  $this->when($this->user_id,[
                'id'      =>  $this->user_id,
                'name'    =>  $this?->user?->company?->name
            ]),
            'permission'  => PermissionResource::collection($this?->permissions)
        ]+$locales;
    }
}
