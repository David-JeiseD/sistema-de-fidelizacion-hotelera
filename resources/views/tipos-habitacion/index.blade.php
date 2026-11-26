@extends('layouts.app')

@section('title', 'Tipos de habitación')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">{{ $hotel->nombre }}</span>
        <h1 class="page-title">Tipos de <em>habitación</em></h1>
        <p class="page-lead">Cada tipo otorga sus puntos al asignar una estadía. Los desactivados no aparecen en recepción, pero su historial se conserva.</p>
    </div>
    <a href="{{ route('tipos-habitacion.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Nuevo tipo</a>
</header>

<section class="panel reveal d1">
    @if ($tipos->isEmpty())
        <div class="empty" style="padding:56px 24px">
            <i class="fas fa-bed" style="font-size:40px"></i>
            <p class="mt-2 mb-4">Este hotel aún no tiene tipos de habitación. Agrégalos para poder asignar estadías.</p>
            <a href="{{ route('tipos-habitacion.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Agregar el primero</a>
        </div>
    @else
        <div class="table-scroll">
            <table class="ledger compacta">
                <thead><tr><th>Tipo</th><th class="t-right">Puntos</th><th class="t-right">Estadías</th><th>Estado</th><th class="t-right">Acciones</th></tr></thead>
                <tbody>
                    @foreach ($tipos as $t)
                        <tr style="{{ $t->activo ? '' : 'opacity:.6' }}">
                            <td><div class="concept"><span class="feed-icon plus" style="width:34px;height:34px;border-radius:10px"><i class="fas fa-bed"></i></span><b>{{ $t->nombre }}</b></div></td>
                            <td class="t-right"><span class="amount plus">+{{ $t->puntos }}</span></td>
                            <td class="t-right num">{{ $t->visitas_count }}</td>
                            <td><span class="pill {{ $t->activo ? 'pill-plus' : '' }}">{{ $t->activo ? 'Activo' : 'Oculto' }}</span></td>
                            <td class="t-right">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('tipos-habitacion.edit', $t) }}" class="btn-hv btn-ghost btn-sm"><i class="fas fa-pen"></i>Editar</a>
                                    @if ($t->visitas_count === 0)
                                        <form method="POST" action="{{ route('tipos-habitacion.destroy', $t) }}" data-confirm="¿Eliminar {{ $t->nombre }}?">
                                            @csrf @method('DELETE')
                                            <button class="btn-hv btn-ghost btn-sm" aria-label="Eliminar {{ $t->nombre }}"><i class="fas fa-trash" style="color:var(--terra)"></i></button>
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
