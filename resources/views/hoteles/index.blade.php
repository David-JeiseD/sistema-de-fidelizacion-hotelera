@extends('layouts.app')

@section('title', 'Hoteles')

@section('content')
@php $lleno = $hoteles->count() >= $empresa->max_hoteles; @endphp
<header class="page-head reveal">
    <div>
        <span class="eyebrow">{{ $empresa->nombre }}</span>
        <h1 class="page-title">Hoteles de la <em>empresa</em></h1>
        <p class="page-lead">Todos comparten el club, sus huéspedes y sus puntos. Cada uno tiene sus habitaciones, su QR de pago y su personal.</p>
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="pill num" title="Hoteles que permite tu plan">{{ $hoteles->count() }} de {{ $empresa->max_hoteles }} en tu plan</span>
        @if ($lleno)
            <span class="btn-hv" aria-disabled="true" title="Tu plan no permite más hoteles"><i class="fas fa-lock"></i>Nuevo hotel</span>
        @else
            <a href="{{ route('hoteles.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Nuevo hotel</a>
        @endif
    </div>
</header>

<div class="qr-cards">
    @foreach ($hoteles as $h)
        @php $activo = $h->id === $hotel->id; @endphp
        <article class="panel panel-pad reveal d{{ min($loop->iteration, 4) }}" style="{{ $activo ? 'border-color:var(--brass);box-shadow:0 0 0 3px rgba(176,138,78,.2),var(--shadow)' : '' }}">
            <div class="row-between">
                <span class="action-icon"><i class="fas fa-hotel"></i></span>
                @if ($activo)<span class="pill pill-brass">Trabajando aquí</span>@endif
            </div>
            <h3 class="mt-3" style="font-size:21px">{{ $h->nombre }}</h3>
            <p class="subtle" style="font-size:13px">{{ collect([$h->direccion, $h->ciudad])->filter()->join(' · ') ?: 'Sin dirección registrada' }}</p>
            <div class="d-flex gap-3 mt-3 subtle" style="font-size:13px">
                <span><i class="fas fa-bed me-1"></i>{{ $h->visitas_count }} estadías</span>
                <span><i class="fas fa-user me-1"></i>{{ $h->usuarios_count }} asignados</span>
            </div>
            <form method="POST" action="{{ route('hoteles.administrar', $h) }}" class="mt-3">
                @csrf
                <button class="btn-hv btn-ghost btn-sm btn-block"><i class="fas fa-sliders"></i>Administrar</button>
            </form>
        </article>
    @endforeach
</div>
@endsection
