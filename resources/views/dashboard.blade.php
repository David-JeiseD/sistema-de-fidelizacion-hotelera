@extends('layouts.app')

@section('title', 'Panel')

@section('content')
<section class="hero reveal">
    @include('partials.rings', ['class' => 'hero-rings'])
    <span class="eyebrow" id="fecha-hoy">Recepción</span>
    <h1 class="mt-3"><span id="saludo">Hola</span>, <em>{{ strtok(Auth::user()->name, ' ') }}</em>.</h1>
    <p>Esto es lo que está pasando en el club de huéspedes. Elige una tarea para empezar el turno.</p>
</section>

<div class="actions">
    <a href="{{ route('clientes.create') }}" class="action reveal d1">
        <span class="action-icon"><i class="fas fa-user-plus"></i></span>
        <div>
            <h3>Registrar huésped</h3>
            <p class="mt-1">Inscribe a un huésped en el club con su DNI, CE o pasaporte.</p>
        </div>
        <span class="action-go">Nuevo registro <i class="fas fa-arrow-right"></i></span>
    </a>
    <a href="{{ route('habitaciones.formulario') }}" class="action reveal d2">
        <span class="action-icon"><i class="fas fa-key"></i></span>
        <div>
            <h3>Asignar habitación</h3>
            <p class="mt-1">Registra la estadía y suma los puntos al instante.</p>
        </div>
        <span class="action-go">Entregar llave <i class="fas fa-arrow-right"></i></span>
    </a>
    <a href="{{ route('clientes.puntos') }}" class="action reveal d3">
        <span class="action-icon"><i class="fas fa-gift"></i></span>
        <div>
            <h3>Puntos y canjes</h3>
            <p class="mt-1">Consulta la libreta de un huésped y canjea recompensas.</p>
        </div>
        <span class="action-go">Abrir libreta <i class="fas fa-arrow-right"></i></span>
    </a>
</div>

<div class="grid grid-4 mt-4">
    @foreach ([
        ['Huéspedes', $stats['clientes'], 'fa-users', 'miembros del club'],
        ['Estadías', $stats['estadias_mes'], 'fa-bed', 'en ' . $mes],
        ['Canjes', $stats['canjes_mes'], 'fa-gift', 'en ' . $mes],
        ['Puntos vigentes', $stats['puntos'], 'fa-star', 'por canjear en total'],
    ] as $i => [$label, $valor, $icono, $pie])
        <div class="panel stat reveal d{{ min($i + 1, 4) }}">
            <div class="stat-label"><i class="fas {{ $icono }}"></i>{{ $label }}</div>
            <div class="stat-value" data-count="{{ $valor }}">{{ number_format($valor, 0, ',', '.') }}</div>
            <div class="stat-foot">{{ $pie }}</div>
        </div>
    @endforeach
</div>

<section class="panel mt-4 reveal d4">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Movimientos recientes</h2>
            <p class="panel-note">Últimas estadías y canjes registrados</p>
        </div>
        <a href="{{ route('clientes.puntos') }}" class="btn-hv btn-ghost btn-sm">Ver libretas</a>
    </div>
    @if ($actividad->isEmpty())
        <div class="empty"><i class="fas fa-feather"></i>Aún no hay movimientos. La primera estadía aparecerá aquí.</div>
    @else
        <ul class="feed">
            @foreach ($actividad as $mov)
                <li>
                    <span class="feed-icon {{ $mov['tipo'] }}"><i class="fas {{ $mov['tipo'] === 'plus' ? 'fa-bed' : 'fa-gift' }}"></i></span>
                    <div style="min-width:0">
                        <div class="feed-title">{{ $mov['cliente'] ?? 'Huésped eliminado' }}</div>
                        <div class="feed-sub">{{ $mov['detalle'] }} · {{ $hotel::enHoraLocal($mov['fecha'], $mov['zona'])->locale('es')->isoFormat('D MMM YYYY') }}</div>
                    </div>
                    <span class="pill {{ $mov['tipo'] === 'plus' ? 'pill-plus' : 'pill-minus' }} num">
                        {{ $mov['tipo'] === 'plus' ? '+' : '−' }}{{ $mov['puntos'] }} pts
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
@endsection

@section('scripts')
<script>
(function () {
    const h = new Date().getHours();
    document.getElementById('saludo').textContent = h < 12 ? 'Buenos días' : h < 19 ? 'Buenas tardes' : 'Buenas noches';
    const f = new Intl.DateTimeFormat('es-PE', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date());
    document.getElementById('fecha-hoy').textContent = f;

    // Contadores que suben al cargar
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('[data-count]').forEach(el => {
        const fin = +el.dataset.count, t0 = performance.now(), dur = 900;
        const fmt = n => n.toLocaleString('es-PE');
        (function paso(t) {
            const p = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(Math.round(fin * e));
            if (p < 1) requestAnimationFrame(paso);
        })(t0);
    });
})();
</script>
@endsection
