<?php

namespace App\Http\Controllers\Api\Dashboard\Vendor\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\General\AuthenticationService;
use App\Http\Requests\Api\General\Auth\LoginRequest;
use App\Http\Requests\Api\General\Auth\LogoutRequest;
use App\Http\Requests\Api\General\Auth\VerifyRequest;
use App\Http\Requests\Api\General\Auth\RegisterRequest;
use App\Http\Requests\Api\General\Auth\ResendOtpRequest;

class AuthController extends Controller
{
    public function __construct(private readonly AuthenticationService $service){}

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->service->login($request);
    }

      public function register(RegisterRequest $request): JsonResponse
    {
        return $this->service->register($request);
    }

    public function verify(VerifyRequest $request): JsonResponse
    {
        return $this->service->verify($request);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $user = $this->service->getUser($request);

        if (is_null($user)) {
            return json(__('Account doest match or record'), status: 'fail', headerStatus: 422);
        }

        $otp = generateOtp(4);

        $user->update([
            'reset_code' => $otp,
            'reset_code_expires_at' => now()->addMinutes(config('auth.code_timeout'))
        ]);

        $this->service->sendOtp($user, $request, $otp, 'verify');

        return json(__('OTP sent successfully'));
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        return $this->service->logout($request);
    }
}
