<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function mostrarFormulario(Request $request)
    {
        $hotel = Hotel::actual();
        $habitaciones = Habitacion::where('hotel_id', $hotel->id)->where('activo', true)->orderBy('puntos')->get();
        $clientes = Cliente::with('persona')->get()->sortBy('nombre');
        // Viene de "Registrar huésped" o de su libreta: se precarga por id (el documento no viaja en la URL)
        $documentoInicial = $request->filled('cliente') ? $clientes->firstWhere('id', (int) $request->cliente)?->dni : null;

        return view('habitaciones.asignar', compact('habitaciones', 'clientes', 'documentoInicial'));
    }

    public function asignar(Request $request)
    {
        $hotel = Hotel::actual();
        $request->validate([
            'documento' => 'required|string|max:20',
            'habitacion_id' => ['required', Rule::exists('habitaciones', 'id')->where('hotel_id', $hotel->id)->where('activo', true)],
        ]);

        $cliente = ClienteController::miembro($request->documento);
        $habitacion = Habitacion::findOrFail($request->habitacion_id);

        // Regla: una estadía por huésped por día y por hotel, con la hora local del hotel.
        // El bloqueo del miembro evita que un doble envío cree dos estadías.
        $asignada = DB::transaction(function () use ($cliente, $habitacion, $hotel) {
            Cliente::lockForUpdate()->find($cliente->id);
            $hoy = $hotel->ahora();
            $yaTiene = Visita::where('cliente_id', $cliente->id)->where('hotel_id', $hotel->id)
                ->whereBetween('fecha', [$hoy->copy()->startOfDay()->utc(), $hoy->copy()->endOfDay()->utc()])
                ->exists();
            if ($yaTiene) {
                return false;
            }

            Visita::create([
                'hotel_id' => $hotel->id,
                'cliente_id' => $cliente->id,
                'habitacion_id' => $habitacion->id,
                'puntos' => $habitacion->puntos,
                'fecha' => now(), // UTC
            ]);

            return true;
        });

        if (! $asignada) {
            return back()->withInput()->with('error', 'Este huésped ya tiene una habitación asignada hoy.');
        }

        return redirect()->route('clientes.libreta', $cliente)->with('success', 'Habitación asignada y puntos acumulados.');
    }
}
