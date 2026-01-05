<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Page;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Page\PageRequest;
use App\Http\Resources\Api\Dashboard\Admin\Page\PageDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Page\PageResource;
use App\Models\Page;

class PageController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Page::class,PageResource::class,PageDetailsResource::class,PageRequest::class);
    }
}
