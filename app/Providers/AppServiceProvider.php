<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // There must be a more elegant way of doing this.
        app()->instance('mbUcFirst', function ($string) {
            return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1, mb_strlen($string)));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
