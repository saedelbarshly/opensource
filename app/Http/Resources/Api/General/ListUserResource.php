<?php

namespace App\Http\Resources\Api\General;

use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Dashboard\Admin\Role\RoleResource;

class ListUserResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return match ($this->user_type) {
            UserType::CLIENT                => $this->clientResource(),
            UserType::VENDOR                => $this->vendorResource(),
            UserType::ADMIN                => $this->adminResource(),
            default                        => $this->baseUserData(),
        };
    }

    private function baseUserData(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'user_type' => $this->user_type,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'avatar'    => $this->avatar,
            'locale'    => $this->locale,
            'is_notify' => $this->is_notify,
            'token'     => $this->when($this->token, $this->token),

        ];
    }

    private function clientResource(): array
    {
        return array_merge($this->baseUserData(), []);
    }
    private function vendorResource(): array
    {
        return array_merge($this->baseUserData(), []);
    }
    private function adminResource(): array
    {
        return array_merge($this->baseUserData(), [
            'role'          => RoleResource::make($this->role),
            'permissions'   => $this->permissions,
        ]);
    }
}
