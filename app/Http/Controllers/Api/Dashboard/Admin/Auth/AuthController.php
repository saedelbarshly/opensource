<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Auth;

use App\Models\User;
use App\Enums\UserType;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\General\PasswordService;
use App\Services\General\AuthenticationService;
use App\Http\Requests\Api\General\Auth\LoginRequest;
use App\Http\Requests\Api\General\Auth\LogoutRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Resources\Api\Dashboard\Admin\Auth\LoginResource;

class AuthController extends Controller
{
    public function __construct(private readonly AuthenticationService $service){}

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->service->login($request);
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        return $this->service->logout($request);
    }
}
