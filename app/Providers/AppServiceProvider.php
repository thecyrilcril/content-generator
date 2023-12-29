<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (in_array(php_uname('s'), ['Windows NT'])) {
            Schema::defaultStringLength(191);
        }

        Model::unguard();

        Model::shouldBeStrict( ! $this->app->isProduction());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
