<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Faq;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Faq\FaqRequest;
use App\Http\Resources\Api\Dashboard\Admin\Faq\FaqDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Faq\FaqResource;
use App\Models\Faq;

class FaqController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Faq::class,FaqResource::class,FaqDetailsResource::class,FaqRequest::class);
    }
}
