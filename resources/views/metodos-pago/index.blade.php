@extends('layouts.app')

@section('title', 'Métodos de pago')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Administración</span>
        <h1 class="page-title">Métodos de <em>pago</em></h1>
        <p class="page-lead">Sube el QR y el número de cada billetera o cuenta del local. Los activos aparecen en la pantalla de cobro.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if ($metodos->where('activo', true)->isNotEmpty())
            <a href="{{ route('cobrar') }}" class="btn-hv btn-ghost"><i class="fas fa-qrcode"></i>Pantalla de cobro</a>
        @endif
        <a href="{{ route('metodos-pago.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Nuevo método</a>
    </div>
</header>

@if ($metodos->isEmpty())
    <section class="panel empty reveal d1" style="padding:56px 24px">
        <i class="fas fa-qrcode" style="font-size:40px"></i>
        <h2 class="panel-title mt-2" style="color:var(--ink)">Aún no hay métodos de pago</h2>
        <p class="mt-2 mb-4">Agrega tu primer QR (por ejemplo, de tu billetera digital) para cobrar en recepción.</p>
        <a href="{{ route('metodos-pago.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Agregar método</a>
    </section>
@else
    <div class="qr-cards">
        @foreach ($metodos as $m)
            <article class="panel qr-card {{ $m->activo ? '' : 'is-off' }} reveal d{{ min($loop->iteration, 4) }}">
                <div class="qr-card-img"><img src="{{ $m->qr_url }}" alt="Código QR de {{ $m->nombre }}" loading="lazy"></div>
                <div class="qr-card-body">
                    <div class="row-between">
                        <h3>{{ $m->nombre }}</h3>
                        <span class="pill {{ $m->activo ? 'pill-plus' : '' }}">{{ $m->activo ? 'Activo' : 'Oculto' }}</span>
                    </div>
                    <div class="qr-num">{{ $m->numero }}</div>
                    <div class="subtle" style="font-size:13px">{{ $m->titular ?: 'Sin titular' }}</div>
                    <div class="qr-card-actions">
                        <a href="{{ route('metodos-pago.edit', $m) }}" class="btn-hv btn-ghost btn-sm flex-grow-1"><i class="fas fa-pen"></i>Editar</a>
                        <form method="POST" action="{{ route('metodos-pago.destroy', $m) }}" data-confirm="¿Eliminar {{ $m->nombre }}? El QR se borrará.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-hv btn-ghost btn-sm" aria-label="Eliminar {{ $m->nombre }}" title="Eliminar"><i class="fas fa-trash" style="color:var(--terra)"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

@section('scripts')
<script>
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
}));
</script>
@endsection
