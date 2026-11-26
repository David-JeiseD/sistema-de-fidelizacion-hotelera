@extends('layouts.app')

@section('title', 'Reportes')

@php
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
    // Escala del gráfico: 4 divisiones con un paso "redondo" (1, 2, 5 × 10ⁿ)
    $maximo = max(1, $tendencia->max('otorgados'), $tendencia->max('canjeados'));
    $bruto = $maximo / 4;
    $potencia = 10 ** floor(log10($bruto));
    $paso = collect([1, 2, 5, 10])->map(fn ($m) => $m * $potencia)->first(fn ($p) => $p >= $bruto);
    $tope = $paso * 4;
    $alto = fn ($v) => round($v / $tope * 100, 2);
    $hayDatos = $tendencia->sum('otorgados') + $tendencia->sum('canjeados') > 0;
    $nombreHotel = $hotelId ? $hoteles->firstWhere('id', $hotelId)->nombre : ($hoteles->count() > 1 ? 'Todos los hoteles' : $hoteles->first()?->nombre);
@endphp

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">{{ $nombreHotel }}</span>
        <h1 class="page-title">Reportes del <em>club</em></h1>
        <p class="page-lead">{{ $periodos[$periodo] }} · {{ $rangoTexto }}. Las cifras se comparan con el periodo anterior de igual duración.</p>
    </div>
</header>

{{-- Filtros: una sola fila, por GET para poder compartir o guardar el enlace --}}
<div class="filtros reveal d1">
    <nav class="segmentado" aria-label="Periodo">
        @foreach ($periodos as $clave => $texto)
            <a href="{{ route('reportes', array_filter(['periodo' => $clave, 'hotel' => $hotelId])) }}" aria-current="{{ $clave === $periodo ? 'true' : 'false' }}">{{ $texto }}</a>
        @endforeach
    </nav>
    @if ($hoteles->count() > 1)
        <form method="GET" action="{{ route('reportes') }}">
            <input type="hidden" name="periodo" value="{{ $periodo }}">
            <label class="visually-hidden" for="hotelFiltro">Hotel</label>
            <select id="hotelFiltro" name="hotel" class="input" style="width:auto;padding:9px 12px" onchange="this.form.submit()">
                <option value="">Todos los hoteles</option>
                @foreach ($hoteles as $h)
                    <option value="{{ $h->id }}" @selected($h->id === $hotelId)>{{ $h->nombre }}</option>
                @endforeach
            </select>
            <noscript><button class="btn-hv btn-sm">Ver</button></noscript>
        </form>
    @endif
</div>

<div class="grid grid-4">
    @foreach ($kpis as $i => [$label, $valor, $anterior, $icono])
        @php
            $cambio = $anterior > 0 ? round(($valor - $anterior) / $anterior * 100) : null;
            $clase = $cambio === null ? 'igual' : ($cambio > 0 ? 'sube' : ($cambio < 0 ? 'baja' : 'igual'));
        @endphp
        <div class="panel stat reveal d{{ min($i + 1, 4) }}">
            <div class="stat-label"><i class="fas {{ $icono }}"></i>{{ $label }}</div>
            <div class="stat-value">{{ $fmt($valor) }}</div>
            <div class="delta {{ $clase }}">
                @if ($cambio === null)
                    {{ $anterior === 0 && $valor > 0 ? 'Sin datos previos para comparar' : 'Igual que el periodo anterior' }}
                @else
                    <i class="fas {{ $cambio > 0 ? 'fa-arrow-up' : ($cambio < 0 ? 'fa-arrow-down' : 'fa-equals') }}"></i>
                    {{ ($cambio > 0 ? '+' : '') . $cambio }}% vs. periodo anterior ({{ $fmt($anterior) }})
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-2 mt-4">
    <div class="panel stat reveal">
        <div class="stat-label"><i class="fas fa-user-plus"></i>Nuevos miembros del club</div>
        <div class="stat-value">{{ $fmt($nuevosMiembros) }}</div>
        <div class="stat-foot">Inscritos en el periodo, en cualquier hotel de la empresa</div>
    </div>
    <div class="panel stat reveal">
        <div class="stat-label"><i class="fas fa-percent"></i>Tasa de canje</div>
        <div class="stat-value">{{ $tasaCanje === null ? '—' : $tasaCanje . '%' }}</div>
        <div class="stat-foot">Puntos canjeados por cada 100 otorgados en el periodo</div>
    </div>
</div>

{{-- Tendencia: dos series del mismo tipo (puntos), un solo eje --}}
<section class="panel mt-4 reveal">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Puntos por mes</h2>
            <p class="panel-note">Últimos 12 meses · otorgados en estadías y canjeados en recompensas</p>
        </div>
        <div class="leyenda" aria-hidden="true">
            <span><i style="background:var(--serie-1)"></i>Otorgados</span>
            <span><i style="background:var(--serie-2)"></i>Canjeados</span>
        </div>
    </div>
    @if ($hayDatos)
        <div class="panel-pad" style="padding-top:18px">
            <div class="grafico" role="group" aria-label="Puntos otorgados y canjeados por mes; los valores también están en la tabla de datos">
                <div class="grafico-area">
                    @foreach (range(4, 0) as $n)
                        <div class="grafico-linea" style="bottom:{{ $n * 25 }}%"><span>{{ $fmt($paso * $n) }}</span></div>
                    @endforeach
                    <div class="grafico-cols">
                        @foreach ($tendencia as $m)
                            {{-- La columna entera es el área de hover/foco: más fácil de apuntar que una barra de 14px --}}
                            <div class="grafico-col" tabindex="0"
                                 data-mes="{{ $m['mesLargo'] }}" data-otorgados="{{ $m['otorgados'] }}" data-canjeados="{{ $m['canjeados'] }}"
                                 aria-label="{{ $m['mesLargo'] }}: {{ $fmt($m['otorgados']) }} puntos otorgados, {{ $fmt($m['canjeados']) }} canjeados">
                                <span class="barra s1" style="height:{{ $alto($m['otorgados']) }}%"></span>
                                <span class="barra s2" style="height:{{ $alto($m['canjeados']) }}%"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="grafico-ejes" aria-hidden="true">
                    @foreach ($tendencia as $m)<span>{{ $m['mes'] }}</span>@endforeach
                </div>
            </div>
        </div>
    @else
        <div class="empty"><i class="fas fa-chart-column"></i>Aún no hay estadías ni canjes en los últimos 12 meses.</div>
    @endif
    <details class="tabla-datos" style="border-top:1px solid var(--line)">
        <summary>Ver como tabla</summary>
        <div class="table-scroll">
            <table class="ledger compacta">
                <thead><tr><th>Mes</th><th class="t-right">Otorgados</th><th class="t-right">Canjeados</th></tr></thead>
                <tbody>
                    @foreach ($tendencia as $m)
                        <tr><td style="text-transform:capitalize">{{ $m['mesLargo'] }}</td><td class="t-right num">{{ $fmt($m['otorgados']) }}</td><td class="t-right num">{{ $fmt($m['canjeados']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</section>

@if ($porHotel->count() > 1)
    <section class="panel mt-4 reveal">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Por hotel</h2>
                <p class="panel-note">{{ $periodos[$periodo] }}</p>
            </div>
        </div>
        <div class="table-scroll">
            <table class="ledger compacta">
                <thead><tr><th>Hotel</th><th class="t-right">Estadías</th><th class="t-right">Huéspedes</th><th class="t-right">Puntos otorgados</th><th class="t-right">Canjes</th><th class="t-right">Puntos canjeados</th></tr></thead>
                <tbody>
                    @foreach ($porHotel->sortByDesc('otorgados') as $h)
                        <tr>
                            <td><b>{{ $h['nombre'] }}</b></td>
                            <td class="t-right num">{{ $fmt($h['estadias']) }}</td>
                            <td class="t-right num">{{ $fmt($h['huespedes']) }}</td>
                            <td class="t-right num">{{ $fmt($h['otorgados']) }}</td>
                            <td class="t-right num">{{ $fmt($h['canjes']) }}</td>
                            <td class="t-right num">{{ $fmt($h['canjeados']) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#fbf7ef">
                        <td><b>Total</b></td>
                        @foreach (['estadias', 'huespedes', 'otorgados', 'canjes', 'canjeados'] as $c)
                            <td class="t-right num"><b>{{ $fmt($c === 'huespedes' ? $kpis[3][1] : $porHotel->sum($c)) }}</b></td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="subtle" style="font-size:12px;padding:10px 28px 16px">El total de huéspedes cuenta a cada persona una vez aunque se haya hospedado en varios hoteles.</p>
    </section>
@endif

<div class="grid grid-3 mt-4">
    @foreach ([['Recompensas más canjeadas', $topRecompensas, 'canjes', 'fa-gift'], ['Habitaciones más usadas', $topHabitaciones, 'estadías', 'fa-bed']] as [$titulo, $datos, $unidad, $icono])
        <section class="panel panel-pad reveal">
            <h2 class="panel-title mb-3" style="font-size:18px"><i class="fas {{ $icono }} me-2 subtle"></i>{{ $titulo }}</h2>
            @if ($datos->isEmpty())
                <p class="subtle" style="font-size:13.5px">Sin {{ $unidad }} en el periodo.</p>
            @else
                <ol class="ranking">
                    @foreach ($datos as $nombre => $cantidad)
                        <li>
                            <span class="r-nombre" title="{{ $nombre }}">{{ $nombre }}</span>
                            <span class="r-valor">{{ $fmt($cantidad) }}</span>
                            <span class="r-pista" aria-hidden="true"><span style="width:{{ round($cantidad / $datos->first() * 100) }}%"></span></span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @endforeach

    <section class="panel panel-pad reveal">
        <h2 class="panel-title mb-3" style="font-size:18px"><i class="fas fa-crown me-2 subtle"></i>Huéspedes más frecuentes</h2>
        @if ($topHuespedes->isEmpty())
            <p class="subtle" style="font-size:13.5px">Sin estadías en el periodo.</p>
        @else
            <ol class="ranking">
                @foreach ($topHuespedes as $h)
                    <li>
                        <a class="r-nombre link" style="border:0" href="{{ route('clientes.libreta', $h['cliente']) }}">{{ $h['nombre'] }}</a>
                        <span class="r-valor">{{ $fmt($h['puntos']) }} pts</span>
                        <span class="subtle" style="grid-column:1/-1;font-size:12px">{{ $h['estadias'] }} {{ $h['estadias'] === 1 ? 'estadía' : 'estadías' }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>

<div class="tooltip-hv" id="tooltip" role="presentation"></div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tip = document.getElementById('tooltip');
    const fmt = n => Number(n).toLocaleString('es-PE');
    // Una sola lectura por mes con ambas series; los textos van con textContent
    function mostrar(barra, x, y) {
        tip.replaceChildren();
        const mes = document.createElement('div');
        mes.className = 't-mes';
        mes.textContent = barra.dataset.mes;
        tip.append(mes);
        [['otorgados', 'Otorgados', 'var(--serie-1)'], ['canjeados', 'Canjeados', 'var(--serie-2)']].forEach(([campo, nombre, color]) => {
            const fila = document.createElement('div');
            fila.className = 't-fila';
            const key = document.createElement('span');
            key.className = 't-key';
            key.style.background = color;
            const valor = document.createElement('b');
            valor.textContent = fmt(barra.dataset[campo]);
            const etiqueta = document.createElement('span');
            etiqueta.textContent = nombre;
            fila.append(key, valor, etiqueta);
            tip.append(fila);
        });
        const ancho = tip.offsetWidth || 180;
        tip.style.left = Math.min(x + 14, window.innerWidth - ancho - 12) + 'px';
        tip.style.top = Math.max(y - 70, 8) + 'px';
        tip.classList.add('visible');
    }
    document.querySelectorAll('.grafico-col').forEach(b => {
        b.addEventListener('pointermove', e => mostrar(b, e.clientX, e.clientY));
        b.addEventListener('pointerleave', () => tip.classList.remove('visible'));
        b.addEventListener('focus', () => { const r = b.getBoundingClientRect(); mostrar(b, r.right, r.top); });
        b.addEventListener('blur', () => tip.classList.remove('visible'));
    });
});
</script>
@endsection
