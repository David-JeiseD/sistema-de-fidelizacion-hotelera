<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CanjeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\HotelActivoController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PlataformaController;

Route::get('/', fn () => redirect()->route('dashboard'));

Auth::routes(['register' => false]);

Route::middleware(['auth', 'empresa.activa'])->group(function () {

    // --- Operación diaria: todo el personal de un hotel ---
    Route::middleware('rol:admin_empresa,gerente,recepcionista')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/home', fn () => redirect()->route('dashboard'));

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes/store', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/puntos', [ClienteController::class, 'formularioPuntos'])->name('clientes.puntos');
        Route::post('/clientes/puntos', [ClienteController::class, 'consultarPuntos'])->name('clientes.puntos.consultar');

        Route::get('/habitaciones/asignar', [HabitacionController::class, 'mostrarFormulario'])->name('habitaciones.formulario');
        Route::post('/habitaciones/asignar', [HabitacionController::class, 'asignar'])->name('habitaciones.asignar');

        Route::get('/canjes/lista/{cliente_id}/{opcion?}', [CanjeController::class, 'lista'])->name('canjes.lista');
        Route::post('/canjes/realizar', [CanjeController::class, 'realizar'])->name('canjes.realizar');

        Route::get('/cobrar', [MetodoPagoController::class, 'cobrar'])->name('cobrar');
        Route::post('/hotel-activo', HotelActivoController::class)->name('hotel-activo');
    });

    // --- Gestión del hotel: gerente y administrador de empresa ---
    Route::middleware('rol:admin_empresa,gerente')->group(function () {
        Route::get('/establecimiento', [EstablecimientoController::class, 'edit'])->name('establecimiento.edit');
        Route::put('/establecimiento', [EstablecimientoController::class, 'update'])->name('establecimiento.update');

        Route::resource('metodos-pago', MetodoPagoController::class)
            ->except('show')
            ->parameters(['metodos-pago' => 'metodo']);

        // Registro de personal (URL heredada)
        Route::get('/9ax1g9', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/9ax1g9', [RegisterController::class, 'register']);
    });

    // --- Plataforma: solo el super-admin ---
    Route::middleware('rol:super_admin')->group(function () {
        Route::get('/plataforma', [PlataformaController::class, 'index'])->name('plataforma');
    });
});
