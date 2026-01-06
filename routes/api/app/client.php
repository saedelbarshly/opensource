<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\Client\Auth\AuthController;
use App\Http\Controllers\Api\App\Client\Auth\PasswordController;
use App\Http\Controllers\Api\App\Client\Profile\ProfileController;
use App\Http\Controllers\Api\App\Client\Notification\NotificationController;



Route::middleware('set_locale')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/logout', 'logout')->name('logout')->middleware(['auth:api']);
    });


    Route::controller(AuthController::class)->group(function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('/login', 'login')->name('login');
            Route::post('/register', 'register')->name('register');
            Route::post('/send', 'resendOtp')->name('resend.Otp');
            Route::post('/verify', 'verify')->name('verify');

            Route::group(['middleware' => ['auth:api']], function () {
                Route::post('logout', 'logout')->name('logout');
                Route::post('/token/refresh', 'refreshToken')->name('refresh.token');
            });
        });
    });

    Route::controller(PasswordController::class)->prefix('password')->group(function () {
        Route::post('/reset', 'reset')->name('reset');
        Route::post('/forget', 'forget')->name('forget');
        Route::post('/verify', 'verify')->name('verify');
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::put('password/change', 'updatePassword')->name('profile.update.password');
            Route::prefix('profile')->group(function () {
                Route::get('/', 'show')->name('profile.show');
                Route::put('/', 'update')->name('profile.update');
                Route::patch('/language/switch/{locale}', 'updateLocale')
                    // ->whereIn('locale', config('translatable.locales'))
                    ->name('profile.update.locale');
                Route::post('send/otp', 'sendOtp')->name('profile.send.otp');
                Route::post('update/auth', 'updateAuth')->name('profile.update.auth');
                Route::patch('notification/switch', 'switchNotification')->name('profile.notification.switch');
            });
        });

        Route::get('notifications/unread-count', [NotificationController::class, 'unReadCount']);
        Route::apiResource('notifications', NotificationController::class);
    });
});
