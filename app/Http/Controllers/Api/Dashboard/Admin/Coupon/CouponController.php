<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Coupon;

use App\Models\Coupon;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Coupon\CouponRequest;
use App\Http\Resources\Api\Dashboard\Admin\Coupon\CouponResource;
use App\Http\Resources\Api\Dashboard\Admin\Coupon\CouponDetailsResource;

class CouponController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Coupon::class, CouponResource::class,CouponDetailsResource::class, CouponRequest::class);
    }
}
