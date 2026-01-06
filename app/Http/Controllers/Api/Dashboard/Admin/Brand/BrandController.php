<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Brand;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Brand\BrandRequest;
use App\Http\Resources\Api\Dashboard\Admin\Brand\BrandDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Brand\BrandResource;
use App\Models\Brand;

class BrandController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Brand::class, BrandResource::class,BrandDetailsResource::class, BrandRequest::class);
    }
}
