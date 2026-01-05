<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1️⃣ Check authentication
        if (! auth('api')->check()) {
            return response()->json([
                'status'  => 'fail',
                'message' => trans('You must log in first.'),
                'data'    => null,
            ], 401);
        }

        /** @var User $user */
        $user = auth('api')->user();

        // 2️⃣ Get current route permission name
        $permission = $request->route()?->getName();

        // 3️⃣ Super users bypass all permissions
        if ($this->isSuperUser($user)) {
            return $next($request);
        }

        // 4️⃣ If route has no permission name → allow
        if (! $permission) {
            return $next($request);
        }

        // 5️⃣ Get user allowed permissions
        $userPermissions = $user->permissions()
            ? $user->permissions()->pluck('back_route_name')->toArray()
            : [];

        // 6️⃣ Check permission
        if (in_array($permission, $userPermissions, true)) {
            return $next($request);
        }

        // 7️⃣ Unauthorized
        return response()->json([
            'status'  => 'fail',
            'message' => 'Unauthorized',
            'data'    => null,
        ], 403);
    }

    protected function isSuperUser(User $user): bool
    {
        return $user->is_super
            && in_array($user->user_type, [
                UserType::ADMIN,
                UserType::VENDOR,
            ], true);
    }
}
