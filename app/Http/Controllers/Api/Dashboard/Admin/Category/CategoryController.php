<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Category;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Category\CategoryRequest;
use App\Http\Resources\Api\Dashboard\Admin\Category\CategoryDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Category\CategoryResource;
use App\Models\Category;

class CategoryController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Category::class, CategoryResource::class, CategoryDetailsResource::class, CategoryRequest::class);
    }

    public function toggleReturnable($id)
    {
        return $this->toggle($id, 'is_returnable');
    }

    public function toggleTaxable($id)
    {
        return $this->toggle($id, 'is_taxable');
    }
}
