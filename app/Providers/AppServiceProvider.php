<?php

namespace App\Providers;

use App\Models\Hotel;
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
        // Datos del hotel del usuario para el menú, el login y las tarjetas.
        View::composer(['layouts.app', 'partials.auth-art', 'partials.member-card', 'tipos-habitacion.*', 'hoteles.*', 'clientes.*'], function ($view) {
            $view->with('hotel', Hotel::actual());
        });
    }
}
