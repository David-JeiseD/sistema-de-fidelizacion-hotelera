@extends('layouts.app')

@section('title', 'Libreta de ' . $cliente->nombre)

@php
    // Libreta: estadías (+) y canjes (−) en orden cronológico con saldo acumulado
    $movimientos = $visitas->map(fn ($v) => ['fecha' => $v->fecha, 'tipo' => 'plus', 'titulo' => 'Estadía', 'detalle' => $v->habitacion?->nombre ?? 'Habitación', 'puntos' => $v->puntos, 'id' => 'v' . $v->id])
        ->concat($canjes->map(fn ($c) => ['fecha' => $c->fecha, 'tipo' => 'minus', 'titulo' => 'Canje', 'detalle' => $c->recompensa?->nombre ?? 'Recompensa', 'puntos' => $c->puntos, 'id' => 'c' . $c->id]))
        ->sortBy([['fecha', 'asc'], ['id', 'asc']])
        ->values();
    $saldo = 0;
    $movimientos = $movimientos->map(function ($m) use (&$saldo) {
        $saldo += $m['tipo'] === 'plus' ? $m['puntos'] : -$m['puntos'];
        return $m + ['saldo' => $saldo];
    })->reverse()->values();

    $ganados = $visitas->sum('puntos');
    $canjeados = $canjes->sum('puntos');
    $progreso = $proxima ? min(100, round($total_puntos / max($proxima->puntos, 1) * 100)) : 100;
@endphp

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Libreta de puntos</span>
        <h1 class="page-title">{{ $cliente->nombre }}</h1>
        <p class="page-lead">Miembro del club desde {{ $cliente->created_at?->locale('es')->isoFormat('MMMM [de] YYYY') ?? 'su primera visita' }}.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('clientes.puntos') }}" class="btn-hv btn-ghost"><i class="fas fa-magnifying-glass"></i>Otra consulta</a>
        <a href="{{ route('habitaciones.formulario', ['dni' => $cliente->dni]) }}" class="btn-hv btn-ghost"><i class="fas fa-key"></i>Asignar habitación</a>
    </div>
</header>

<div class="split split-card">
    <div class="stack reveal d1">
        @include('partials.member-card', [
            'nombre' => $cliente->nombre,
            'dni' => $cliente->dni,
            'puntos' => $total_puntos,
            'desde' => $cliente->created_at?->format('m/Y') ?? '—',
        ])
    </div>

    <div class="stack">
        <section class="panel reveal d2">
            <div class="summary">
                <div><div class="k">Ganados</div><div class="v" style="color:var(--sage)">+{{ number_format($ganados, 0, ',', '.') }}</div></div>
                <div><div class="k">Canjeados</div><div class="v" style="color:var(--terra)">−{{ number_format($canjeados, 0, ',', '.') }}</div></div>
                <div><div class="k">Estadías</div><div class="v">{{ $visitas->count() }}</div></div>
            </div>
        </section>

        <section class="panel panel-pad reveal d3">
            @if ($proxima)
                <div class="row-between">
                    <span class="eyebrow">Próxima recompensa</span>
                    <span class="pill pill-brass num">{{ $progreso }}%</span>
                </div>
                <h2 class="panel-title mt-2">{{ $proxima->nombre }}</h2>
                <div class="progress-hv mt-3" role="progressbar" aria-valuenow="{{ $progreso }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progreso hacia {{ $proxima->nombre }}">
                    <span style="width:{{ $progreso }}%"></span>
                </div>
                <p class="muted mt-2" style="font-size:13.5px">Le faltan <b class="num">{{ $proxima->puntos - $total_puntos }}</b> puntos de {{ $proxima->puntos }}.</p>
            @else
                <span class="eyebrow">Todo desbloqueado</span>
                <h2 class="panel-title mt-2">Puede canjear cualquier recompensa</h2>
            @endif

            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-3" style="border-top:1px dashed var(--line)">
                <span class="muted" style="font-size:13.5px">
                    @if ($disponibles)
                        <b class="num" style="color:var(--ink)">{{ $disponibles }}</b> {{ $disponibles === 1 ? 'recompensa disponible' : 'recompensas disponibles' }} ahora
                    @else
                        Aún no alcanza para ninguna recompensa
                    @endif
                </span>
                <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}" class="btn-hv btn-brass">
                    <i class="fas fa-gift"></i>Ver recompensas
                </a>
            </div>
        </section>
    </div>
</div>

<section class="panel mt-4 reveal d4">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Historial</h2>
            <p class="panel-note">Estadías y canjes, del más reciente al más antiguo</p>
        </div>
        <span class="pill num">{{ $movimientos->count() }} movimientos</span>
    </div>
    @if ($movimientos->isEmpty())
        <div class="empty">
            <i class="fas fa-book-open"></i>
            La libreta está en blanco. Asígnale una habitación para registrar su primera estadía.
        </div>
    @else
        <div class="table-scroll">
            <table class="ledger">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Concepto</th>
                        <th scope="col" class="t-right">Movimiento</th>
                        <th scope="col" class="t-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientos as $m)
                        <tr>
                            <td class="date">{{ \Carbon\Carbon::parse($m['fecha'])->format('d/m/Y') }}</td>
                            <td>
                                <div class="concept">
                                    <span class="feed-icon {{ $m['tipo'] }}" style="width:34px;height:34px;border-radius:10px"><i class="fas {{ $m['tipo'] === 'plus' ? 'fa-bed' : 'fa-gift' }}"></i></span>
                                    <span><b>{{ $m['detalle'] }}</b><small>{{ $m['titulo'] }}</small></span>
                                </div>
                            </td>
                            <td class="t-right"><span class="amount {{ $m['tipo'] }}">{{ $m['tipo'] === 'plus' ? '+' : '−' }}{{ $m['puntos'] }}</span></td>
                            <td class="t-right"><span class="balance">{{ number_format($m['saldo'], 0, ',', '.') }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
