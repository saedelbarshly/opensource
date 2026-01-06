<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Dashboard\Admin\Faq\FaqController;
use App\Http\Controllers\Api\Dashboard\Admin\Auth\AuthController;
use App\Http\Controllers\Api\Dashboard\Admin\City\CityController;
use App\Http\Controllers\Api\Dashboard\Admin\Page\PageController;
use App\Http\Controllers\Api\Dashboard\Admin\Auth\PasswordController;
use App\Http\Controllers\Api\Dashboard\Admin\Country\CountryController;
use App\Http\Controllers\Api\Dashboard\Admin\Profile\ProfileController;
use App\Http\Controllers\Api\Dashboard\Admin\Notification\NotificationController;
use App\Http\Controllers\Api\Dashboard\Admin\Notification\SentNotificationController;



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
            Route::put('password/change', 'updatePassword')->name('profile.update.password');
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

        Route::get('notifications/unread-count', [NotificationController::class, 'unReadCount']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications', [NotificationController::class, 'store']);
        Route::delete('notifications', [NotificationController::class, 'destroy']);

        Route::get('sender-notifications/users', [SentNotificationController::class, 'users']);
        Route::apiResource('sender-notifications', SentNotificationController::class);


        Route::get('countries/list', [CountryController::class, 'getWithoutPagination']);
        Route::patch('countries/{country}/toggle-active', [CountryController::class, 'toggleActive'])->name('countries.toggle-active');
        Route::apiResource('countries', CountryController::class);

        Route::get('cities/list', [CityController::class, 'getWithoutPagination']);
        Route::patch('cities/{city}/toggle-active', [CityController::class, 'toggleActive'])->name('cities.toggle-active');
        Route::apiResource('cities', CityController::class);

        Route::get('pages/list', [PageController::class, 'getWithoutPagination']);
        Route::patch('pages/{page}/toggle-active', [PageController::class, 'toggleActive'])->name('pages.toggle-active');
        Route::apiResource('pages', PageController::class);

        Route::get('faqs/list', [FaqController::class, 'getWithoutPagination']);
        Route::patch('faqs/{faq}/toggle-active', [FaqController::class, 'toggleActive'])->name('faqs.toggle-active');
        Route::apiResource('faqs', FaqController::class);
    });
});
