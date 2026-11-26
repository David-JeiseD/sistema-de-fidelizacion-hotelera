<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Visita;

class DashboardController extends Controller
{
    public function index()
    {
        $inicioMes = now()->startOfMonth();

        $stats = [
            'clientes' => Cliente::count(),
            'estadias_mes' => Visita::where('fecha', '>=', $inicioMes)->count(),
            'canjes_mes' => Canje::where('fecha', '>=', $inicioMes)->count(),
            'puntos' => Visita::sum('puntos') - Canje::sum('puntos'),
        ];

        $visitas = Visita::with('cliente', 'habitacion')->latest('fecha')->take(6)->get()
            ->map(fn ($v) => ['tipo' => 'plus', 'fecha' => $v->fecha, 'cliente' => $v->cliente?->nombre, 'detalle' => 'Estadía · ' . $v->habitacion?->nombre, 'puntos' => $v->puntos]);

        $canjes = Canje::with('cliente', 'recompensa')->latest('fecha')->take(6)->get()
            ->map(fn ($c) => ['tipo' => 'minus', 'fecha' => $c->fecha, 'cliente' => $c->cliente?->nombre, 'detalle' => 'Canje · ' . $c->recompensa?->nombre, 'puntos' => $c->puntos]);

        $actividad = $visitas->concat($canjes)->sortByDesc('fecha')->take(6)->values();

        return view('dashboard', compact('stats', 'actividad'));
    }
}
