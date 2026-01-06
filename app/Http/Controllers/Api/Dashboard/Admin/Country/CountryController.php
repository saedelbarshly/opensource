<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Country;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\Country\CountryRequest;
use App\Http\Resources\Api\Dashboard\Admin\Country\CountryDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\Country\CountryResource;
use App\Models\Country;

class CountryController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(Country::class,CountryDetailsResource::class,CountryDetailsResource::class,CountryRequest::class);
    }
}
