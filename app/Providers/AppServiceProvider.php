<?php

namespace App\Providers;

use App\Models\Establecimiento;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Datos del hotel para el menú, el login y las tarjetas (una consulta por petición).
        View::composer(['layouts.app', 'partials.auth-art', 'partials.member-card'], function ($view) {
            $view->with('hotel', once(fn () => Establecimiento::actual()));
        });
    }
}
