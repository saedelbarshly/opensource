<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->header('user_type')) {
            return json(__('User type not found, please add user-type header'), status: 'fail', headerStatus: 422);
        }elseif(!in_array($request->header('user_type'), UserType::values())) {
            return json(__('this user type not found'), status: 'fail', headerStatus: 422);
        }
        return $next($request);
    }
}
