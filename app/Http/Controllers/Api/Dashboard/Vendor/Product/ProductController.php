<?php

namespace App\Http\Controllers\Api\Dashboard\Vendor\Product;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Product\ProductRequest;
use App\Http\Resources\Api\Dashboard\Admin\Product\ProductDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Product\ProductResource;
use App\Models\Product;

class ProductController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Product::class, ProductResource::class, ProductDetailsResource::class, ProductRequest::class);
    }

    public function toggleActive($id)
    {
        return $this->toggle($id, 'is_active');
    }
}
