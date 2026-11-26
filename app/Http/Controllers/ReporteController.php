<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Hotel;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Reportes de la cadena: consolidado de los hoteles permitidos o uno solo, por periodo.
 * shortcut: agrega en PHP (filas del periodo y de 12 meses) para respetar la zona horaria de
 * cada hotel sin SQL específico de MySQL/SQLite; si una empresa supera ~100k estadías al año,
 * pasar a agregados en SQL o a una tabla resumen diaria.
 */
class ReporteController extends Controller
{
    public const PERIODOS = [
        'mes' => 'Este mes',
        'mes_anterior' => 'Mes anterior',
        '30d' => 'Últimos 30 días',
        'anio' => 'Este año',
    ];

    public function index(Request $request)
    {
        $hotelActual = Hotel::actual();
        $ahora = $hotelActual->ahora();
        $hoteles = Hotel::permitidos()->orderBy('nombre')->get(['id', 'nombre', 'zona_horaria']);

        $periodo = array_key_exists($request->periodo, self::PERIODOS) ? $request->periodo : 'mes';
        $hotelId = $hoteles->firstWhere('id', (int) $request->hotel)?->id; // ignora hoteles no permitidos
        $ids = $hotelId ? collect([$hotelId]) : $hoteles->pluck('id');

        [$desde, $hasta] = $this->rango($periodo, $ahora);
        $duracion = $desde->diffInSeconds($hasta);
        [$desdeAnt, $hastaAnt] = [$desde->copy()->subSeconds($duracion + 1), $desde->copy()->subSecond()];

        $visitas = $this->visitas($ids, $desde, $hasta)->load('cliente.persona', 'habitacion');
        $canjes = $this->canjes($ids, $desde, $hasta)->load('recompensa');
        $visitasAnt = $this->visitas($ids, $desdeAnt, $hastaAnt);
        $canjesAnt = $this->canjes($ids, $desdeAnt, $hastaAnt);

        $kpis = [
            ['Estadías', $visitas->count(), $visitasAnt->count(), 'fa-bed'],
            ['Puntos otorgados', $visitas->sum('puntos'), $visitasAnt->sum('puntos'), 'fa-star'],
            ['Canjes', $canjes->count(), $canjesAnt->count(), 'fa-gift'],
            ['Huéspedes activos', $visitas->unique('cliente_id')->count(), $visitasAnt->unique('cliente_id')->count(), 'fa-users'],
        ];
        $nuevosMiembros = Cliente::whereBetween('created_at', [$desde->copy()->utc(), $hasta->copy()->utc()])->count();
        $tasaCanje = $visitas->sum('puntos') > 0 ? (int) round($canjes->sum('puntos') / $visitas->sum('puntos') * 100) : null;

        $porHotel = $hoteles->whereIn('id', $ids)->map(fn ($h) => [
            'nombre' => $h->nombre,
            'estadias' => $visitas->where('hotel_id', $h->id)->count(),
            'otorgados' => $visitas->where('hotel_id', $h->id)->sum('puntos'),
            'canjes' => $canjes->where('hotel_id', $h->id)->count(),
            'canjeados' => $canjes->where('hotel_id', $h->id)->sum('puntos'),
            'huespedes' => $visitas->where('hotel_id', $h->id)->unique('cliente_id')->count(),
        ])->values();

        return view('reportes.index', [
            'periodos' => self::PERIODOS,
            'periodo' => $periodo,
            'hoteles' => $hoteles,
            'hotelId' => $hotelId,
            'rangoTexto' => $desde->locale('es')->isoFormat('D MMM YYYY') . ' – ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'kpis' => $kpis,
            'nuevosMiembros' => $nuevosMiembros,
            'tasaCanje' => $tasaCanje,
            'porHotel' => $porHotel,
            'tendencia' => $this->tendencia($ids, $ahora),
            'topRecompensas' => $this->ranking($canjes, fn ($c) => $c->recompensa?->nombre ?? 'Recompensa eliminada'),
            'topHabitaciones' => $this->ranking($visitas, fn ($v) => $v->habitacion?->nombre ?? 'Habitación eliminada'),
            'topHuespedes' => $visitas->groupBy('cliente_id')->map(fn ($g) => [
                'nombre' => $g->first()->cliente?->nombre ?? '—',
                'cliente' => $g->first()->cliente,
                'estadias' => $g->count(),
                'puntos' => $g->sum('puntos'),
            ])->sortByDesc('puntos')->take(8)->values(),
        ]);
    }

    /** Inicio y fin del periodo en la hora del hotel. */
    private function rango(string $periodo, Carbon $ahora): array
    {
        return match ($periodo) {
            'mes_anterior' => [$ahora->copy()->subMonthNoOverflow()->startOfMonth(), $ahora->copy()->subMonthNoOverflow()->endOfMonth()],
            '30d' => [$ahora->copy()->subDays(29)->startOfDay(), $ahora->copy()->endOfDay()],
            'anio' => [$ahora->copy()->startOfYear(), $ahora->copy()->endOfDay()],
            default => [$ahora->copy()->startOfMonth(), $ahora->copy()->endOfDay()],
        };
    }

    private function visitas(Collection $ids, Carbon $desde, Carbon $hasta)
    {
        return Visita::whereIn('hotel_id', $ids)->whereBetween('fecha', [$desde->copy()->utc(), $hasta->copy()->utc()])->get();
    }

    private function canjes(Collection $ids, Carbon $desde, Carbon $hasta)
    {
        return Canje::whereIn('hotel_id', $ids)->whereBetween('fecha', [$desde->copy()->utc(), $hasta->copy()->utc()])->get();
    }

    /** Puntos otorgados y canjeados por mes, últimos 12 meses (hora del hotel). */
    private function tendencia(Collection $ids, Carbon $ahora): Collection
    {
        $inicio = $ahora->copy()->subMonthsNoOverflow(11)->startOfMonth();
        $mesDe = fn ($fila) => Hotel::enHoraLocal($fila->fecha, $ahora->timezoneName)->format('Y-m');
        $otorgados = $this->visitas($ids, $inicio, $ahora->copy()->endOfDay())->groupBy($mesDe);
        $canjeados = $this->canjes($ids, $inicio, $ahora->copy()->endOfDay())->groupBy($mesDe);

        return collect(range(0, 11))->map(function ($i) use ($inicio, $otorgados, $canjeados) {
            $mes = $inicio->copy()->addMonthsNoOverflow($i);
            $clave = $mes->format('Y-m');

            return [
                'mes' => rtrim($mes->locale('es')->isoFormat('MMM'), '.'),
                'mesLargo' => $mes->locale('es')->isoFormat('MMMM YYYY'),
                'otorgados' => (int) ($otorgados[$clave] ?? collect())->sum('puntos'),
                'canjeados' => (int) ($canjeados[$clave] ?? collect())->sum('puntos'),
            ];
        });
    }

    private function ranking(Collection $filas, callable $nombre): Collection
    {
        return $filas->groupBy($nombre)->map->count()->sortDesc()->take(5);
    }
}
