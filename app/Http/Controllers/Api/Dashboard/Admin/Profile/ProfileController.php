<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Profile;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\General\AuthenticationService;
use App\Http\Resources\Api\General\ShowProfileResource;
use App\Http\Requests\Api\General\Profile\EditAuthRequest;
use App\Http\Requests\Api\General\Profile\UpdateAuthRequest;
use App\Http\Requests\Api\General\Profile\UpdateProfileRequest;
use App\Http\Requests\Api\General\Profile\UpdatePasswordRequest;

class ProfileController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        $this->user = auth('api')->user();
    }

    public function show(Request $request): JsonResponse
    {
        return json(ShowProfileResource::make($this->user));
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->user->update($request->only('password'));
        return json(ShowProfileResource::make($this->user->refresh()), __('Password was updated successfully'));
    }

    public function sendOtp(EditAuthRequest $request): JsonResponse
    {
        $authService = new AuthenticationService();

        $otp = generateOtp(4);

        $this->user->authVerifications()->updateOrCreate([
            $request->auth_type => $request->auth
        ], [
            'reset_code' => $otp,
            'phone_code' => $request->auth_type == 'phone' ? $request->phone_code : null
        ]);

        $authService->SendOtp($this->user, $request, $otp, 'update_' . $request->auth_type);

        return json(__('OTP sent successfully'));
    }

    public function updateAuth(UpdateAuthRequest $request): JsonResponse
    {
        $this->user->update([
            $request->auth_type => $request->auth,
            'phone_code' => $request->auth_type == 'phone' ? $request->phone_code : $this->user->phone_code
        ]);

        $this->user->authVerifications()->where($request->auth_type, $request->auth)->delete();

        return json(ShowProfileResource::make($this->user->refresh()), __('Auth was updated successfully'));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $this->user->update($request->validated());

        return json(ShowProfileResource::make($this->user->refresh()));
    }

    public function updateLocale(string $locale): JsonResponse
    {
        $this->user->update([
            'locale' => $locale
        ]);

        return json(ShowProfileResource::make($this->user->refresh()));
    }

    public function switchNotification(): JsonResponse
    {
        $this->user->update([
            'is_notify' => !$this->user->is_notify
        ]);
        return json(ShowProfileResource::make($this->user->refresh()));
    }


    public function getMyPermissions()
    {
        $user = auth('api')->user();
        if ($user->is_super) {
            $permissions = Permission::pluck('back_route_name');
        } else {
            $permissions  =  $user->role ? $user->role?->permissions()->pluck('back_route_name') : [];
        }
        $groupedPermissions = $user->getPermissions($permissions);
        return json($groupedPermissions, status: 'success', headerStatus: 200);
    }
}
