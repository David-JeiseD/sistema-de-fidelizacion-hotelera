<?php

namespace App\Http\Controllers;
use App\Models\Recompensa;
use App\Models\Habitacion;
use App\Models\Cliente;
use App\Models\Visita;
use App\Models\Canje;


use Illuminate\Http\Request;

class RecompensaController extends Controller
{
    public function index()
    {
        $recompensas = Recompensa::all();
        return view('recompensas.index', compact('recompensas'));
    }

    public function create()
    {
        return view('recompensas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'puntos_requeridos' => 'required|integer|min:1',
        ]);

        Recompensa::create($request->all());
        return redirect()->route('recompensas.index')->with('success', 'Recompensa registrada correctamente');
    }
}