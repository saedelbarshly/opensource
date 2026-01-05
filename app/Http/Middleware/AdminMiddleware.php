<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('api')->check()
            && in_array(auth('api')->user()->user_type, [UserType::ADMIN])
            && auth('api')->user()->is_active
            && !auth('api')->user()->is_banned
        ) {
            return $next($request);
        } else {
            return json(null, trans('You are not authorized to make this action'), 'fail', 403);
        }
    }
}
