<?php

namespace Modules\Media\Providers;

use Modules\Media\Models\Media;
use Illuminate\Support\ServiceProvider;
use Modules\Media\Observers\MediaObserver;

class MediaServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Media::observe(MediaObserver::class);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
