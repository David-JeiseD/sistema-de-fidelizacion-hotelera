<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Hotel;
use App\Models\Visita;

class DashboardController extends Controller
{
    public function index()
    {
        $inicioMes = now()->startOfMonth();
        $empresa = Hotel::actual()->empresa;
        $hoteles = $empresa->hoteles()->pluck('id');
        $visitasEmpresa = fn () => Visita::whereIn('hotel_id', $hoteles);
        $canjesEmpresa = fn () => Canje::whereIn('hotel_id', $hoteles);

        $stats = [
            'clientes' => Cliente::where('empresa_id', $empresa->id)->count(),
            'estadias_mes' => $visitasEmpresa()->where('fecha', '>=', $inicioMes)->count(),
            'canjes_mes' => $canjesEmpresa()->where('fecha', '>=', $inicioMes)->count(),
            'puntos' => $visitasEmpresa()->sum('puntos') - $canjesEmpresa()->sum('puntos'),
        ];

        $visitas = $visitasEmpresa()->with('cliente.persona', 'habitacion')->latest('fecha')->take(6)->get()
            ->map(fn ($v) => ['tipo' => 'plus', 'fecha' => $v->fecha, 'cliente' => $v->cliente?->nombre, 'detalle' => 'Estadía · ' . $v->habitacion?->nombre, 'puntos' => $v->puntos]);

        $canjes = $canjesEmpresa()->with('cliente.persona', 'recompensa')->latest('fecha')->take(6)->get()
            ->map(fn ($c) => ['tipo' => 'minus', 'fecha' => $c->fecha, 'cliente' => $c->cliente?->nombre, 'detalle' => 'Canje · ' . $c->recompensa?->nombre, 'puntos' => $c->puntos]);

        $actividad = $visitas->concat($canjes)->sortByDesc('fecha')->take(6)->values();

        return view('dashboard', compact('stats', 'actividad'));
    }
}
