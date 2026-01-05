<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\General\AuthenticationService;
use App\Http\Requests\Api\General\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\General\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\General\Auth\VerifyForgetPasswordRequest;

class PasswordController extends Controller
{
    public function __construct(private readonly AuthenticationService $service){}

    public function forget(ForgetPasswordRequest $request): JsonResponse
    {
        return $this->service->forget($request);
    }

    public function verify(VerifyForgetPasswordRequest $request): JsonResponse
    {
        return response()->json(__('Verified successfully'));   
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        return $this->service->reset($request);
    }
}
