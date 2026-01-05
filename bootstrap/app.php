<?php

use App\Http\Middleware\{
    AdminMiddleware,
    PermissionMiddleware,
    SetLocale,
    AgentTokenMiddleware,
    ClientMiddleware,
    DriverMiddleware,
    OtpMiddleware,
    SetUserTypeMiddleware
};

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::group([
                'prefix'        => 'api',
                'namespace'     => 'App\Http\Controllers\Api',
                'middleware'    => ['api', 'set_locale', 'cors','user_type'],
            ], function () {
                Route::prefix('general')->group(base_path('routes/api/general/general.php'))->withoutMiddleware(['api','user_type']);
                Route::prefix('dashboard/admin')->group(base_path('routes/api/dashboard/admin.php'));
                Route::prefix('dashboard/vendor')->group(base_path('routes/api/dashboard/vendor.php'));
                Route::prefix('app/client')->group(base_path('routes/api/app/client.php'));
            });
        },
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            SetLocale::class,
            HandleCors::class,
        ]);
        $middleware->alias([
            'set_locale'            => SetLocale::class,
            'cors'                  => HandleCors::class,
            'admin'                 => AdminMiddleware::class,
            'permission'            => PermissionMiddleware::class,
            // 'agent_token'           => AgentTokenMiddleware::class,
            // 'client'                => ClientMiddleware::class,
            // 'otp'                   => OtpMiddleware::class
            'user_type'                => SetUserTypeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            # Handle web HTTP exceptions
            if ($e instanceof HttpException && !$request->wantsJson()) {
                return match ($e->getStatusCode()) {
                    Response::HTTP_FORBIDDEN    => response()->view('errors.404', [], 403),
                    default                     => response()->view('errors.404', [], 404),
                };
            }

            # Handle Throttle exceptions
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return json(__('Too many requests, please try again after a while!'), status: 'fail', headerStatus: 429);
            }

            # Handle API HTTP exceptions
            if ($e instanceof HttpException && $request->wantsJson()) {
                return match ($e->getStatusCode()) {
                    Response::HTTP_FORBIDDEN    => json(__('You are not authorized to make this action'), status: 'fail', headerStatus: 403),
                    default                     => json($e->getMessage(), status: 'fail', headerStatus: 404),
                };
            }

            # Model not found (web)
            if ($e instanceof ModelNotFoundException && auth('api')->check() && in_array(auth('api')->user()->user_type, ['super_admin']) && !$request->ajax() && !$request->wantsJson()) {
                return response()->view('errors.404', [], 404);
            }

            # Model not found (API)
            if ($e instanceof ModelNotFoundException && $request->wantsJson()) {
                return json(__('Data not found'), status: 'fail', headerStatus: 404);
            }

            # Unauthenticated (API)
            if ($e instanceof AuthenticationException && $request->wantsJson()) {
                return json(__('Unauthenticated, you have to login first'), status: 'fail', headerStatus: 401);
            }

            # fallback: return null to let Laravel handle
            return null;
        });

        $exceptions->report(function (Throwable $e) {
            if (app()->isProduction()) {
                if ($this->shouldReport($e)) {
                    try {
                        Log::channel('slack')->error('🚨 Server Error Detected', [
                            'app'       => config('app.name'),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'url'       => request()->fullUrl(),
                            'method'    => request()->method(),
                            'ip'        => request()->ip(),
                            'user'      => auth('api')->check() ? ['id' => auth('api')->user()->id, 'name' => auth('api')->user()->name, 'phone' => auth('api')->user()->phone] : 'guest',
                            'input'     => request()->except(['password', 'password_confirmation', 'current_password']),
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to send error to Slack: ' . $e->getMessage());
                    }
                }
            }
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('attachment:clean')->withoutOverlapping()->dailyAt('00:00');
        $schedule->command('notifications:delete')->withoutOverlapping()->dailyAt('00:00');
        $schedule->command('telescope:prune')->withoutOverlapping()->everySixHours();
    })
    ->create();
