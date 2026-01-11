<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
            'code'              => $this->code,
            'value'             => $this->value,
            'type'              => $this->type,
            'start_at'          => $this->start_at?->format('Y-m-d'),
            'end_at'            => $this->end_at?->format('Y-m-d'),
            'min_order_total'   => $this->min_order_total,
            'max_order_total'   => $this->max_order_total,
            'limit'             => $this->limit,
            'limit_for_user'    => $this->limit_for_user,
            'used_count'        => $this->used_count,
            'is_active'         => $this->is_active
        ];
    }
}
