@extends('layouts.app')

@section('title', 'Recompensas')

@php
    $puntos = $cliente->total_puntos;
    $iconoRecompensa = function ($nombre) {
        $n = mb_strtolower($nombre);
        return match (true) {
            str_contains($n, 'vino') => 'fa-wine-bottle',
            str_contains($n, 'cerveza') => 'fa-beer-mug-empty',
            str_contains($n, 'jarra') => 'fa-jar',
            str_contains($n, 'jugo'), str_contains($n, 'vaso') => 'fa-glass-water',
            str_contains($n, 'transporte'), str_contains($n, 'aeropuerto') => 'fa-van-shuttle',
            str_contains($n, 'desayuno') => 'fa-mug-saucer',
            str_contains($n, 'noche'), str_contains($n, 'habitación') => 'fa-bed',
            default => 'fa-gift',
        };
    };
    $ini = collect(explode(' ', trim($cliente->nombre)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
@endphp

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Canje de puntos</span>
        <h1 class="page-title">Recompensas <em>del club</em></h1>
        <p class="page-lead">Elige un vale y confirma el canje. Los puntos se descuentan de la libreta al instante.</p>
    </div>
    <form method="POST" action="{{ route('clientes.puntos.consultar') }}">
        @csrf
        <input type="hidden" name="documento" value="{{ $cliente->dni }}">
        <button class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver a la libreta</button>
    </form>
</header>

<section class="panel member-strip reveal d1">
    <span class="avatar" aria-hidden="true">{{ $ini }}</span>
    <div style="min-width:0">
        <div class="fw-bold text-truncate">{{ $cliente->nombre }}</div>
        <div class="subtle num text-nowrap" style="font-size:13px">{{ $cliente->tipo_documento }} {{ $cliente->dni }}</div>
    </div>
    <div class="pts">
        <b>{{ number_format($puntos, 0, ',', '.') }}</b>
        <small>puntos disponibles</small>
    </div>
</section>

@if ($recompensaSeleccionada)
    @php $alcanza = $puntos >= $recompensaSeleccionada->puntos; @endphp
    <section class="stub mt-4 reveal d2" id="confirmar">
        @include('partials.rings', ['class' => 'hero-rings'])
        <span class="eyebrow">Talón de canje</span>
        <h2 class="mt-2">{{ $recompensaSeleccionada->nombre }}</h2>
        <div class="stub-rows" style="max-width:520px">
            <div class="stub-row"><span>Huésped</span><b style="font-family:var(--sans);font-size:15px">{{ $cliente->nombre }}</b></div>
            <div class="stub-row"><span>Saldo actual</span><b>{{ number_format($puntos, 0, ',', '.') }}</b></div>
            <div class="stub-row"><span>Costo del vale</span><b>−{{ number_format($recompensaSeleccionada->puntos, 0, ',', '.') }}</b></div>
            <div class="stub-row total"><span>Saldo después</span><b>{{ number_format($puntos - $recompensaSeleccionada->puntos, 0, ',', '.') }}</b></div>
        </div>
        <div class="d-flex gap-2 flex-wrap position-relative">
            @if ($alcanza)
                <form method="POST" action="{{ route('canjes.realizar') }}" id="canjeForm">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="recompensa_id" value="{{ $recompensaSeleccionada->id }}">
                    <button type="submit" class="btn-hv btn-brass btn-lg" id="confirmarBtn"><i class="fas fa-check"></i>Confirmar canje</button>
                </form>
            @else
                <span class="pill pill-minus">Puntos insuficientes para este vale</span>
            @endif
            <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}" class="btn-hv btn-ghost btn-lg">Cancelar</a>
        </div>
    </section>
@endif

<div class="row-between mt-5 mb-3">
    <h2 class="panel-title" style="font-size:24px">Vales disponibles</h2>
    <span class="subtle" style="font-size:13px">{{ $recompensas->filter(fn ($r) => $puntos >= $r->puntos)->count() }} de {{ $recompensas->count() }} al alcance</span>
</div>

@if ($recompensas->isEmpty())
    <div class="panel empty"><i class="fas fa-gift"></i>No hay recompensas configuradas todavía.</div>
@else
    <div class="vouchers">
        @foreach ($recompensas->sortBy('puntos') as $i => $r)
            @php
                $ok = $puntos >= $r->puntos;
                $sel = $recompensaSeleccionada && $recompensaSeleccionada->id === $r->id;
                $pct = $ok ? 100 : (int) round($puntos / max($r->puntos, 1) * 100);
            @endphp
            <article class="voucher {{ $ok ? 'is-ready' : 'is-locked' }} {{ $sel ? 'is-selected' : '' }} reveal d{{ min($loop->iteration, 4) }}">
                <div class="voucher-top">
                    <span class="voucher-icon"><i class="fas {{ $iconoRecompensa($r->nombre) }}"></i></span>
                    <div style="min-width:0">
                        <h3>{{ $r->nombre }}</h3>
                        <div class="voucher-cost">{{ number_format($r->puntos, 0, ',', '.') }}<small>pts</small></div>
                    </div>
                </div>
                <div class="voucher-perf"></div>
                <div class="voucher-bottom">
                    @if ($ok)
                        <div class="voucher-status" style="color:var(--sage)"><span><i class="fas fa-circle-check me-1"></i>Disponible</span></div>
                        @if ($sel)
                            <a href="#confirmar" class="btn-hv btn-sm btn-block btn-brass"><i class="fas fa-arrow-up"></i>Seleccionado</a>
                        @else
                            <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id, 'opcion' => $r->id]) }}#confirmar" class="btn-hv btn-sm btn-block">Elegir este vale</a>
                        @endif
                    @else
                        <div class="voucher-status subtle"><span><i class="fas fa-lock me-1"></i>Faltan {{ $r->puntos - $puntos }}</span><span class="num">{{ $pct }}%</span></div>
                        <div class="progress-hv" aria-hidden="true"><span style="width:{{ $pct }}%"></span></div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('canjeForm');
    if (form) form.addEventListener('submit', function () {
        const btn = document.getElementById('confirmarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Procesando…';
    });
});
</script>
@endsection
