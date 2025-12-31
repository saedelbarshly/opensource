<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\City;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Dashboard\Admin\City\CityRequest;
use App\Http\Resources\Api\Dashboard\Admin\City\CityDetailsResource;
use App\Http\Resources\Api\Dashboard\Admin\City\CityResource;
use App\Models\City;

class CityController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct(City::class,CityResource::class,CityDetailsResource::class,CityRequest::class);
    }
}
