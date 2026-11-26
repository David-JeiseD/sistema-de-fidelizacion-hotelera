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
        $hotel = Hotel::actual();
        $inicioMes = $hotel->ahora()->startOfMonth()->utc();
        // Métricas de toda la empresa (cadena): el scope limita cada consulta a sus hoteles.
        $visitasEmpresa = fn () => Visita::query();
        $canjesEmpresa = fn () => Canje::query();

        $stats = [
            'clientes' => Cliente::count(),
            'estadias_mes' => $visitasEmpresa()->where('fecha', '>=', $inicioMes)->count(),
            'canjes_mes' => $canjesEmpresa()->where('fecha', '>=', $inicioMes)->count(),
            'puntos' => $visitasEmpresa()->sum('puntos') - $canjesEmpresa()->sum('puntos'),
        ];

        $visitas = $visitasEmpresa()->with('cliente.persona', 'habitacion', 'hotel')->latest('fecha')->take(6)->get()
            ->map(fn ($v) => ['tipo' => 'plus', 'fecha' => $v->fecha, 'zona' => $v->hotel?->zona_horaria, 'cliente' => $v->cliente?->nombre, 'detalle' => 'Estadía · ' . $v->habitacion?->nombre, 'puntos' => $v->puntos]);

        $canjes = $canjesEmpresa()->with('cliente.persona', 'recompensa', 'hotel')->latest('fecha')->take(6)->get()
            ->map(fn ($c) => ['tipo' => 'minus', 'fecha' => $c->fecha, 'zona' => $c->hotel?->zona_horaria, 'cliente' => $c->cliente?->nombre, 'detalle' => 'Canje · ' . $c->recompensa?->nombre, 'puntos' => $c->puntos]);

        $actividad = $visitas->concat($canjes)->sortByDesc('fecha')->take(6)->values();

        $mes = $hotel->ahora()->locale('es')->isoFormat('MMMM');

        return view('dashboard', compact('stats', 'actividad', 'mes', 'hotel'));
    }
}
