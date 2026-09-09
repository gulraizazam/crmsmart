<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
       
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // HandleExceptions resets error_reporting(-1); keep PHP 8.4 vendor deprecations off the page.
        if (PHP_VERSION_ID >= 80400) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        }
    }
}
