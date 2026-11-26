<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    // Método para mostrar el formulario de asignación de habitación
    public function mostrarFormulario()
    {
        $hotel = Hotel::actual();
        $habitaciones = Habitacion::where('hotel_id', $hotel->id)->orderBy('puntos')->get();
        $clientes = Cliente::with('persona')->get()->sortBy('nombre');
        return view('habitaciones.asignar', compact('habitaciones', 'clientes'));
    }

    public function asignar(Request $request)
    {
        $hotel = Hotel::actual();
        $request->validate([
            'documento' => 'required|string|max:20',
            'habitacion_id' => ['required', Rule::exists('habitaciones', 'id')->where('hotel_id', $hotel->id)],
        ]);

        $cliente = ClienteController::miembro($request->documento);
        $habitacion = Habitacion::findOrFail($request->habitacion_id);

        // Regla de negocio: una estadía por huésped por día en cada hotel.
        if (Visita::where('cliente_id', $cliente->id)->where('hotel_id', $hotel->id)->whereDate('fecha', today())->exists()) {
            return back()->withInput()->with('error', 'Este huésped ya tiene una habitación asignada hoy.');
        }

        Visita::create([
            'hotel_id' => $hotel->id,
            'cliente_id' => $cliente->id,
            'habitacion_id' => $habitacion->id,
            'puntos' => $habitacion->puntos,
            'fecha' => now(),
        ]);

        return redirect()->route('clientes.puntos', ['documento' => $cliente->dni])->with('success', 'Habitación asignada y puntos acumulados.');
    }
}
