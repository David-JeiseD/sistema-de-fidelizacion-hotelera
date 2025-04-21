<?php

namespace App\Http\Controllers;
use App\Models\Cliente;
use App\Models\Visita;
use App\Models\Habitacion;
use App\Models\Canje;



use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function show($dni)
    {
        $cliente = Cliente::where('dni', $dni)->firstOrFail();
        $visitas = Visita::where('cliente_id', $cliente->id)->get();
        return view('visitas.show', compact('cliente', 'visitas'));
    }
}