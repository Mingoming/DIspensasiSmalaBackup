<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Date;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default date format for Carbon instances to dd/mm/yy
        Date::macro('toDefaultFormat', function () {
            return $this->format('d/m/y');
        });

        Date::macro('toFullFormat', function () {
            return $this->format('d/m/Y');
        });
    }
}
