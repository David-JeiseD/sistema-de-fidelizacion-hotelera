<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Recompensa;

class CanjeController extends Controller
{
    /**
     * Realiza un canje de recompensa para un cliente.
     */
    public function realizar(Request $request)
    {
        // Buscar el cliente por ID
        $cliente = Cliente::find($request->cliente_id);

        // Validar que el cliente exista
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Buscar la recompensa seleccionada
        $recompensa = Recompensa::findOrFail($request->recompensa_id);

        // Verificar si el cliente tiene suficientes puntos
        if ($cliente->total_puntos >= $recompensa->puntos) {
            try {
                // Registrar el canje en la base de datos
                Canje::create([
                    'cliente_id' => $cliente->id,
                    'recompensa_id' => $recompensa->id,
                    'fecha' => now(),
                    'puntos' => $recompensa->puntos,
                ]);

                // Restar los puntos del cliente y guardar en la base de datos
                $cliente->total_puntos -= $recompensa->puntos;
                $cliente->save();

                // Redirigir con un mensaje de éxito
                return redirect()->route('canjes.lista', ['cliente_id' => $cliente->id])
                    ->with('success', '¡Canje realizado con éxito!');
            } catch (\Exception $e) {
                // Registrar el error en los logs y redirigir con un mensaje de error
                \Log::error('Error al registrar el canje: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Ocurrió un error al registrar el canje');
            }
        }

        // Si no tiene suficientes puntos, redirigir con un mensaje de error
        return redirect()->back()->with('error', 'No tienes puntos suficientes para esta recompensa');
    }
}