<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Dashboard\Admin\Auth\AuthController;
use App\Http\Controllers\Api\Dashboard\Admin\Auth\PasswordController;
use App\Http\Controllers\Api\Dashboard\Admin\Profile\ProfileController;



Route::middleware('set_locale')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/logout', 'logout')->name('logout')->middleware(['auth:api']);
    });

    Route::controller(PasswordController::class)->prefix('password')->group(function () {
        Route::post('/reset', 'reset')->name('reset');
        Route::post('/forget', 'forget')->name('forget');
        Route::post('/verify', 'verify')->name('verify');
    });

    Route::group(['middleware' => ['auth:api', 'admin']], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::put('password/update', 'updatePassword')->name('profile.update.password');
            Route::prefix('profile')->group(function () {
                Route::get('/', 'show')->name('profile.show');
                Route::get('/permissions', 'getMyPermissions')->name('profile.permissions');
                Route::put('/', 'update')->name('profile.update');
                Route::patch('/language/switch/{locale}', 'updateLocale')
                    // ->whereIn('locale', config('translatable.locales'))
                    ->name('profile.update.locale');
                Route::post('send/otp', 'sendOtp')->name('profile.send.otp');
                Route::post('update/auth', 'updateAuth')->name('profile.update.auth');
                Route::patch('notification/switch', 'switchNotification')->name('profile.notification.switch');
            });
        });
    });
});
