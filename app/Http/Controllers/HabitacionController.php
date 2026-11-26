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
        $request->validate([
            'dni' => 'required|exists:clientes,dni',
            'habitacion_id' => 'required|exists:habitaciones,id',
        ], [
            'dni.exists' => 'No existe un cliente con ese DNI.',
        ]);

        $cliente = Cliente::where('dni', $request->dni)->firstOrFail();
        $habitacion = Habitacion::findOrFail($request->habitacion_id);

        // Regla de negocio: una visita (estadía) por cliente por día.
        if (Visita::where('cliente_id', $cliente->id)->whereDate('fecha', today())->exists()) {
            return back()->withInput()->with('error', 'Este cliente ya tiene una habitación asignada hoy.');
        }

        Visita::create([
            'cliente_id' => $cliente->id,
            'habitacion_id' => $habitacion->id,
            'puntos' => $habitacion->puntos,
            'fecha' => now(),
        ]);

        return redirect()->route('clientes.puntos', ['dni' => $cliente->dni])->with('success', 'Habitación asignada y puntos acumulados.');
    }
}
