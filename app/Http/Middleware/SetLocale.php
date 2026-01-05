<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestLanguage = $request->header('Accept-Language');
        $locales         = config('translatable.locales');
        $default         = config('translatable.default');

        if ($requestLanguage && in_array($requestLanguage, $locales)) {
            app()->setLocale($requestLanguage);
        } else {
            app()->setLocale($default);
        }

        return $next($request);
    }
}
