<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Hotel;
use App\Models\Recompensa;

class CanjeController extends Controller
{
    /**
     * Catálogo de recompensas para un miembro, con la elegida (opcional) para confirmar.
     */
    public function lista($cliente_id, $opcion = null)
    {
        $hotel = Hotel::actual();
        $cliente = Cliente::with('persona')->where('empresa_id', $hotel->empresa_id)->find($cliente_id);

        if (!$cliente) {
            return redirect()->route('clientes.puntos')->with('error', 'Cliente no encontrado');
        }

        $recompensas = Recompensa::disponiblesEn($hotel)->get();
        $recompensaSeleccionada = $opcion ? $recompensas->firstWhere('id', (int) $opcion) : null;

        return view('canjes.lista', compact('recompensas', 'cliente', 'recompensaSeleccionada'));
    }

    /**
     * Realiza un canje de recompensa para un cliente.
     */
    public function realizar(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'recompensa_id' => 'required|exists:recompensas,id',
        ]);

        $hotel = Hotel::actual();
        // Solo recompensas válidas en este hotel y miembros de esta empresa.
        $recompensa = Recompensa::disponiblesEn($hotel)->findOrFail($request->recompensa_id);

        // Bloqueo la fila del cliente: dos canjes simultáneos se ejecutan uno tras otro
        // y el segundo ve los puntos ya descontados.
        $ok = DB::transaction(function () use ($request, $recompensa, $hotel) {
            $cliente = Cliente::where('empresa_id', $hotel->empresa_id)->lockForUpdate()->findOrFail($request->cliente_id);

            if ($cliente->total_puntos < $recompensa->puntos) {
                return false;
            }

            // El saldo se calcula de visitas - canjes (accessor), no se guarda aparte.
            Canje::create([
                'hotel_id' => $hotel->id,
                'cliente_id' => $cliente->id,
                'recompensa_id' => $recompensa->id,
                'fecha' => now(),
                'puntos' => $recompensa->puntos,
            ]);

            return true;
        });

        if (!$ok) {
            return redirect()->back()->with('error', 'No tienes puntos suficientes para esta recompensa');
        }

        return redirect()->route('canjes.lista', ['cliente_id' => $request->cliente_id])
            ->with('success', '¡Canje realizado con éxito!');
    }
}
