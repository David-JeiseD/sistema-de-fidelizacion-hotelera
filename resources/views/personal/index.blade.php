@extends('layouts.app')

@section('title', 'Personal')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Administración</span>
        <h1 class="page-title">Personal</h1>
        <p class="page-lead">Quién trabaja en tus hoteles y qué puede hacer. Desactiva un acceso en vez de borrarlo: su historial se conserva.</p>
    </div>
    <a href="{{ route('register') }}" class="btn-hv"><i class="fas fa-user-plus"></i>Nuevo trabajador</a>
</header>

<section class="panel reveal d1">
    <div class="table-scroll">
        <table class="ledger compacta">
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Hoteles</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="t-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($personal as $u)
                    @php $esYo = $u->is(auth()->user()); @endphp
                    <tr style="{{ $u->activo ? '' : 'opacity:.6' }}">
                        <td>
                            <div class="concept">
                                <span class="avatar" style="width:36px;height:36px;font-size:12px" aria-hidden="true">{{ collect(explode(' ', trim($u->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('') }}</span>
                                <span><b>{{ $u->name }}</b>@if($esYo) <span class="pill pill-brass" style="font-size:10px">Tú</span>@endif<small>{{ $u->email }}</small></span>
                            </div>
                        </td>
                        <td>{{ $u->rol_nombre }}</td>
                        <td class="subtle" style="font-size:13px">{{ $u->esAdmin() ? 'Todos' : ($u->hoteles->pluck('nombre')->join(', ') ?: '—') }}</td>
                        <td><span class="pill {{ $u->activo ? 'pill-plus' : 'pill-minus' }}">{{ $u->activo ? 'Activo' : 'Desactivado' }}</span></td>
                        <td class="t-right">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('personal.edit', $u) }}" class="btn-hv btn-ghost btn-sm"><i class="fas fa-pen"></i>{{ $esYo ? 'Mis datos' : 'Editar' }}</a>
                                @unless ($esYo)
                                    <form method="POST" action="{{ route('personal.estado', $u) }}" data-confirm="{{ $u->activo ? '¿Quitar el acceso a ' . $u->name . '?' : '¿Devolver el acceso a ' . $u->name . '?' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-hv btn-ghost btn-sm" title="{{ $u->activo ? 'Desactivar' : 'Reactivar' }}" aria-label="{{ $u->activo ? 'Desactivar' : 'Reactivar' }} a {{ $u->name }}">
                                            <i class="fas {{ $u->activo ? 'fa-user-slash' : 'fa-user-check' }}" style="color:{{ $u->activo ? 'var(--terra)' : 'var(--sage)' }}"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
}));
</script>
@endsection
