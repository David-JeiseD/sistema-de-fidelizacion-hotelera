<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'recompensa_id' => 'required|exists:recompensas,id',
        ]);

        $recompensa = Recompensa::findOrFail($request->recompensa_id);

        // Bloqueo la fila del cliente: dos canjes simultáneos se ejecutan uno tras otro
        // y el segundo ve los puntos ya descontados.
        $ok = DB::transaction(function () use ($request, $recompensa) {
            $cliente = Cliente::lockForUpdate()->findOrFail($request->cliente_id);

            if ($cliente->total_puntos < $recompensa->puntos) {
                return false;
            }

            // El saldo se calcula de visitas - canjes (accessor), no se guarda aparte.
            Canje::create([
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
