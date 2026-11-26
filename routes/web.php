<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\CanjeController;
use App\Http\Controllers\RecompensaController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\MetodoPagoController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Auth::routes(['register' => false]);

// Ruta para mostrar el formulario de registro
Route::get('/9ax1g9', [RegisterController::class, 'showRegistrationForm'])->name('register');

// Ruta para procesar el registro
Route::post('/9ax1g9', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes/store', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/puntos', [ClienteController::class, 'formularioPuntos'])->name('clientes.puntos');
    
    Route::post('/clientes/puntos', [ClienteController::class, 'consultarPuntos'])->name('clientes.puntos.consultar');

    // Habitaciones
    Route::post('/habitaciones/asignar', [HabitacionController::class, 'asignar'])->name('habitaciones.asignar');
    Route::get('/habitaciones/asignar', [HabitacionController::class, 'mostrarFormulario'])->name('habitaciones.formulario');

    Route::get('/canjes/lista/{cliente_id}/{opcion?}', [CanjeController::class, 'lista'])->name('canjes.lista');
    
    
    Route::post('/canjes/realizar', [CanjeController::class, 'realizar'])->name('canjes.realizar');
    
    
    
    // Hotel activo (cadenas con varios locales)
    Route::post('/hotel-activo', \App\Http\Controllers\HotelActivoController::class)->name('hotel-activo');

    // Establecimiento
    Route::get('/establecimiento', [EstablecimientoController::class, 'edit'])->name('establecimiento.edit');
    Route::put('/establecimiento', [EstablecimientoController::class, 'update'])->name('establecimiento.update');

    // Pagos con QR
    Route::get('/cobrar', [MetodoPagoController::class, 'cobrar'])->name('cobrar');
    Route::resource('metodos-pago', MetodoPagoController::class)
        ->except('show')
        ->parameters(['metodos-pago' => 'metodo']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });
});


