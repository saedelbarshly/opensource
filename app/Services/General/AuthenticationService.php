<?php

namespace App\Services\General;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Enums\UserType;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Api\General\ShowProfileResource;

class AuthenticationService
{
    public function login($request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = $this->getUser($request);

            if (is_null($user)) {
                return json(__('Account does not exist'), status: 'fail', headerStatus: 422);
            }

            if (!Hash::check($request->password, $user->password)) {
                return json(__('Invalid credentials'), status: 'fail', headerStatus: 422);
            }

            if (!$user->is_active) {

                $otp = generateOtp(4);

                $user->update([
                    'reset_code' => $otp
                ]);

                $this->SendOtp($user, $request, $otp, 'verify');

                return json(ShowProfileResource::make($user),__('Please verify your account'));
            }

            if ($user->is_banned || $user->isBanned()) {
                return json(__('Account banned, please contact support'), status: 'fail', headerStatus: 403);
            }

            if($request->device_token && $request->device_type){
                $this->createDevice($request, $user);
            }

            DB::commit();

            $token = auth('api')->login($user);

            data_set($user, 'token', $token);

            return json(ShowProfileResource::make($user), __('Logged in successfully'));

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return json(__('Server error'), status: 'fail', headerStatus: 500);
        }
    }

    public function register($request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user_type = request()->header('user_type');

            $otp = generateOtp(4);

            $request->merge([
                'reset_code' => $otp,
                'user_type' => $user_type,
                'reset_code_expires_at' => now()->addMinutes(config('auth.code_timeout'))
            ]);



            $user = User::query()->updateOrCreate([
                'email' => $request->email,
                'user_type' => $user_type,
                'phone' => $request->phone
            ], $request->all());


            $this->sendOtp($user, $request, $otp, 'verify');

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return json(__('Server error'), status: 'fail', headerStatus: 500);
        }

        return json(__('Account created successfully, code was sent'));
    }

    /**
     * Send OTP via Email or SMS based on user type
     *
     * @param User $user
     * @param mixed $request
     * @param string|null $otp
     * @param string|null $type (verify, update_password, update_email)
     */
    public function sendOtp($user, $request, $otp = null, $type = null): void
    {
        $userType = $request->header('user_type') ?? $user->user_type;
        $authType = UserType::authType($userType);

        if ($authType === 'email') {
            $this->sendOtpViaEmail($user, $request, $otp, $type);
        } else {
            $this->sendOtpViaSms($user, $otp, $type);
        }
    }

    /**
     * Send OTP via Email
     */
    private function sendOtpViaEmail($user, $request, $otp, $type): void
    {
        // $email = $type === 'update_email' ? $request->auth : $user->email;

        // if (!$email) {
        //     Log::warning('Attempted to send email OTP but no email address available', [
        //         'user_id' => $user->id,
        //         'type' => $type
        //     ]);
        //     return;
        // }

        // $mailable = match ($type) {
        //     'verify' => new \App\Mail\VerifyNewUser($user),
        //     'update_password' => new \App\Mail\ForgetPassword($user),
        //     'update_email' => new \App\Mail\UpdateEmail($user, $otp),
        //     default => null,
        // };

        // if ($mailable) {
        //     Mail::to($email)->send($mailable);
        // }
    }

    /**
     * Send OTP via SMS
     * TODO: Implement SMS gateway integration
     */
    private function sendOtpViaSms($user, $otp, $type): void
    {
        // TODO: Integrate SMS gateway (e.g., Twilio, Vonage, local provider)
        // Example implementation:
        // $message = match ($type) {
        //     'verify' => __('Your ZADA verification code is: :otp', ['otp' => $otp]),
        //     'update_password' => __('Your ZADA password reset code is: :otp', ['otp' => $otp]),
        //     default => __('Your ZADA code is: :otp', ['otp' => $otp]),
        // };
        // SmsService::send($user->phone_code . $user->phone, $message);

        Log::info('SMS OTP would be sent', [
            'user_id' => $user->id,
            'phone' => $user->phone_code . $user->phone,
            'type' => $type,
            'otp' => $otp, // Remove in production!
        ]);
    }

    public function verify($request): JsonResponse
    {
        $user = $this->getUser($request);

        try {

            DB::beginTransaction();

            if ($user->reset_code != $request->code) {

                return json(__('OTP Code is wrong'), status: 'fail', headerStatus: 422);
            }

            $user->update([
                'reset_code' => null,
                'is_active' => true,
            ]);

            if ($user->is_banned || $user->isBanned()) {
                return json(__('Account banned, please contact support'), status: 'fail', headerStatus: 403);
            }

            if ($request->device_token && $request->device_type) {
                $this->createDevice($request, $user);
            }

            $token = auth('api')->login($user);

            data_set($user, 'token', $token);

            DB::commit();

            return json(ShowProfileResource::make($user), __('Your account has been activated successfully'));

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return json(__('Server error'), status: 'fail', headerStatus: 500);
        }
    }

    public function logout($request): JsonResponse
    {
        try {
            $user = auth('api')->user();

            $agent_token = $request->header('agent_token');

            $user->devices()->where('agent_token', $agent_token)->update(
                [
                    'status' => false
                ]);

        } catch (\Exception $exception) {
            Log::error($exception);
        }

        auth("api")->logout(true);

        return json(
            __('User Logged out successfully')
        );
    }

    public function refreshToken($request): JsonResponse
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return json(__('User not authenticated'), status: 'fail', headerStatus: 401);
            }

            // Update device token if provided
            if ($request->device_token) {
                $user->devices()->updateOrCreate(
                    $request->only('device_type') + ['device_token' => $request->old_device_token],
                    $request->only('device_type', 'device_token')
                );
            }

            // Refresh JWT token
            $newToken = auth('api')->refresh();

            return json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ], __('Token refreshed successfully'));

        } catch (\Exception $exception) {
            Log::error($exception);
            return json(__('Could not refresh token'), status: 'fail', headerStatus: 401);
        }
    }


    public function forget($request): JsonResponse
    {
        try {
            $user = $this->getUser($request);

            $otp = generateOtp(4);

            $user->update([
                'reset_code' => $otp,
                'reset_code_expires_at' => now()->addMinutes(config('auth.code_timeout'))
            ]);

            $this->sendOtp($user, $request, $otp, 'update_password');

            return json(__('Code has been sent to you.'));
        } catch (\Exception $exception) {
            Log::error($exception);
            return json(__('Server error'), status: 'fail', headerStatus: 500);
        }
    }

    public function reset($request): JsonResponse
    {
        try {
            $user = $this->getUser($request);

            if ($user->reset_code_expires_at->isPast()) {
                return json(null, trans('Code expired'), 'fail', 400);
            }

            $user->update([
                'password' => $request->password,
                'reset_code' => null,
                'reset_code_expires_at' => null
            ]);

            return json(__('Password updated successfully'));

        } catch (\Exception $exception) {
            Log::error($exception);
            return json(__('Server error'), status: 'fail', headerStatus: 500);
        }
    }

    public function getUser($request): ?User
    {
        $user_type = $request->header('user_type');

        return User::where($request->auth_type, $request->auth)
            ->where('user_type', $user_type)
            ->when($request->phone_code, fn($query) => $query->where('phone_code', $request->phone_code))
            ->first();

    }

    private function createDevice($request, $user): void
    {
        try {
            $agent_token = $request->header('agent_token');
            Device::updateOrCreate(
                [
                    'agent_token' => $agent_token,
                    'user_id' => $user->id
                ],
                [
                    'device_token' => $request->device_token,
                    'device_type' => $request->device_type,
                    'status' => true
                ]
            );

        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }

}