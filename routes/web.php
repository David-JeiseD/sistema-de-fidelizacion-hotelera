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
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\RecompensaController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\TipoHabitacionController;
use App\Http\Controllers\ReporteController;

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
        Route::get('/clientes/{cliente}/libreta', [ClienteController::class, 'libreta'])->name('clientes.libreta');

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

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');

        Route::resource('tipos-habitacion', TipoHabitacionController::class)
            ->except('show')
            ->parameters(['tipos-habitacion' => 'tipo']);

        // Personal (el alta usa la URL heredada /9ax1g9)
        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::get('/personal/{usuario}/editar', [PersonalController::class, 'edit'])->name('personal.edit');
        Route::put('/personal/{usuario}', [PersonalController::class, 'update'])->name('personal.update');
        Route::patch('/personal/{usuario}/estado', [PersonalController::class, 'estado'])->name('personal.estado');
        Route::get('/9ax1g9', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/9ax1g9', [RegisterController::class, 'register']);
    });

    // --- Toda la empresa: solo el administrador ---
    Route::middleware('rol:admin_empresa')->group(function () {
        Route::resource('recompensas', RecompensaController::class)->except('show');
        Route::get('/hoteles', [HotelController::class, 'index'])->name('hoteles.index');
        Route::get('/hoteles/crear', [HotelController::class, 'create'])->name('hoteles.create');
        Route::post('/hoteles', [HotelController::class, 'store'])->name('hoteles.store');
        Route::post('/hoteles/{hotel}/administrar', [HotelController::class, 'administrar'])->name('hoteles.administrar');
    });

    // --- Plataforma: solo el super-admin ---
    Route::middleware('rol:super_admin')->group(function () {
        Route::get('/plataforma', [PlataformaController::class, 'index'])->name('plataforma');
        Route::get('/plataforma/empresas/crear', [PlataformaController::class, 'create'])->name('plataforma.empresas.create');
        Route::post('/plataforma/empresas', [PlataformaController::class, 'store'])->name('plataforma.empresas.store');
        Route::patch('/plataforma/empresas/{empresa}', [PlataformaController::class, 'update'])->name('plataforma.empresas.update');
    });
});
