@extends('layouts.app')

@section('title', 'Recompensas')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Catálogo del club</span>
        <h1 class="page-title">Recompensas</h1>
        <p class="page-lead">Valen en todos los hoteles de la empresa, salvo las marcadas como exclusivas de un local. Las ocultas no se ofrecen al canjear.</p>
    </div>
    <a href="{{ route('recompensas.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Nueva recompensa</a>
</header>

<section class="panel reveal d1">
    @if ($recompensas->isEmpty())
        <div class="empty" style="padding:56px 24px">
            <i class="fas fa-gift" style="font-size:40px"></i>
            <p class="mt-2 mb-4">El catálogo está vacío. Agrega recompensas para que tus huéspedes canjeen sus puntos.</p>
            <a href="{{ route('recompensas.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Agregar la primera</a>
        </div>
    @else
        <div class="table-scroll">
            <table class="ledger compacta">
                <thead><tr><th>Recompensa</th><th class="t-right">Puntos</th><th>Dónde vale</th><th class="t-right">Canjes</th><th>Estado</th><th class="t-right">Acciones</th></tr></thead>
                <tbody>
                    @foreach ($recompensas as $r)
                        <tr style="{{ $r->activo ? '' : 'opacity:.6' }}">
                            <td><div class="concept"><span class="voucher-icon" style="width:34px;height:34px;border-radius:10px;font-size:14px"><i class="fas fa-gift"></i></span><b>{{ $r->nombre }}</b></div></td>
                            <td class="t-right"><span class="balance">{{ number_format($r->puntos, 0, ',', '.') }}</span></td>
                            <td class="subtle" style="font-size:13px">{{ $r->hotel?->nombre ? 'Solo ' . $r->hotel->nombre : 'Todos los hoteles' }}</td>
                            <td class="t-right num">{{ $r->canjes_count }}</td>
                            <td><span class="pill {{ $r->activo ? 'pill-plus' : '' }}">{{ $r->activo ? 'Activa' : 'Oculta' }}</span></td>
                            <td class="t-right">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('recompensas.edit', $r) }}" class="btn-hv btn-ghost btn-sm"><i class="fas fa-pen"></i>Editar</a>
                                    @if ($r->canjes_count === 0)
                                        <form method="POST" action="{{ route('recompensas.destroy', $r) }}" data-confirm="¿Eliminar {{ $r->nombre }}?">
                                            @csrf @method('DELETE')
                                            <button class="btn-hv btn-ghost btn-sm" aria-label="Eliminar {{ $r->nombre }}"><i class="fas fa-trash" style="color:var(--terra)"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@section('scripts')
<script>
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));
</script>
@endsection
