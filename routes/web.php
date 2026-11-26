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

    Route::get('/canjes/lista/{cliente_id}/{opcion?}', function ($cliente_id, $opcion = null) {
        $recompensas = \App\Models\Recompensa::all();
        $cliente = \App\Models\Cliente::find($cliente_id);
    
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }
    
        $recompensaSeleccionada = $opcion ? \App\Models\Recompensa::find($opcion) : null;
    
        return view('canjes.lista', compact('recompensas', 'cliente', 'recompensaSeleccionada'));
    })->name('canjes.lista');
    
    
    Route::post('/canjes/realizar', [CanjeController::class, 'realizar'])->name('canjes.realizar');
    
    
    
    // Establecimiento
    Route::get('/establecimiento', [EstablecimientoController::class, 'edit'])->name('establecimiento.edit');
    Route::put('/establecimiento', [EstablecimientoController::class, 'update'])->name('establecimiento.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });
});


