<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Modules\Notification\Broadcasting\FCMChannel;
use Modules\Notification\Broadcasting\RedisChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Modules\Notification\Broadcasting\GroupNotification;
use GuzzleHttp\Client;
use Modules\Notification\Services\FcmClient;
use Modules\Notification\Services\FcmTokenProvider;


class NotificationServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(FcmTokenProvider::class, function () {
            return new FcmTokenProvider(new Client());
        });

        $this->app->singleton(FcmClient::class, function ($app) {
            return new FcmClient(
                new Client(),
                $app->make(FcmTokenProvider::class)
            );
        });

        Notification::extend('redis', function ($app) {
            return $app->make(RedisChannel::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->instance(DatabaseChannel::class, new GroupNotification());
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
