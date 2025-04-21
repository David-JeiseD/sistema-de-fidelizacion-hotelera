<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Visita;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    // Método para mostrar el formulario de asignación de habitación
    public function mostrarFormulario()
    {
        $habitaciones = Habitacion::all();
        return view('habitaciones.asignar', compact('habitaciones'));
    }

    public function asignar(Request $request)
{
    $cliente = Cliente::where('dni', $request->dni)->firstOrFail();
    $habitacion = Habitacion::findOrFail($request->habitacion_id);

    // Registrar la visita con la fecha actual
    Visita::create([
        'cliente_id' => $cliente->id,
        'habitacion_id' => $habitacion->id,
        'puntos' => $habitacion->puntos,
        'fecha' => now(), // Usamos la función `now()` para obtener la fecha y hora actual
    ]);

    return redirect()->route('clientes.puntos', ['dni' => $cliente->dni])->with('success', 'Habitación asignada y puntos acumulados.');
}
}

