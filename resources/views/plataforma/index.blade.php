@extends('layouts.app')

@section('title', 'Plataforma')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Plataforma</span>
        <h1 class="page-title">Empresas <em>clientes</em></h1>
        <p class="page-lead">Todas las cadenas y hoteles que usan el sistema. Da de alta clientes, ajusta cuántos hoteles incluye su plan y suspende cuentas.</p>
    </div>
    <a href="{{ route('plataforma.empresas.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Nueva empresa</a>
</header>

<div class="grid grid-4 mb-4">
    @foreach ([
        ['Empresas', $empresas->count(), 'fa-building'],
        ['Hoteles', $empresas->sum('hoteles_count'), 'fa-hotel'],
        ['Membresías', $empresas->sum('clientes_count'), 'fa-id-card'],
        ['Personas únicas', $personas, 'fa-user'],
    ] as $i => [$label, $valor, $icono])
        <div class="panel stat reveal d{{ $i + 1 }}">
            <div class="stat-label"><i class="fas {{ $icono }}"></i>{{ $label }}</div>
            <div class="stat-value">{{ number_format($valor, 0, ',', '.') }}</div>
        </div>
    @endforeach
</div>

<section class="panel reveal d4">
    <div class="panel-head">
        <h2 class="panel-title">Empresas</h2>
        <span class="pill num">{{ $empresas->count() }}</span>
    </div>
    <div class="table-scroll">
        <table class="ledger compacta">
            <thead>
                <tr>
                    <th scope="col">Empresa</th>
                    <th scope="col" class="t-right">Hoteles</th>
                    <th scope="col" class="t-right">Miembros</th>
                    <th scope="col" class="t-right">Personal</th>
                    <th scope="col">Plan y estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empresas as $e)
                    <tr>
                        <td>
                            <div class="concept">
                                <span class="brand-seal" style="width:34px;height:34px"><img src="{{ $e->logo_url }}" alt=""></span>
                                <span><b>{{ $e->nombre }}</b><small>{{ $e->ruc ? 'RUC ' . $e->ruc : 'Sin RUC' }} · {{ $e->nombre_club }}</small></span>
                            </div>
                        </td>
                        <td class="t-right num">{{ $e->hoteles_count }}</td>
                        <td class="t-right num">{{ $e->clientes_count }}</td>
                        <td class="t-right num">{{ $e->usuarios_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('plataforma.empresas.update', $e) }}" class="d-flex align-items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="visually-hidden" for="max-{{ $e->id }}">Hoteles del plan de {{ $e->nombre }}</label>
                                <input id="max-{{ $e->id }}" name="max_hoteles" type="number" min="{{ max(1, $e->hoteles_count) }}" value="{{ $e->max_hoteles }}" class="input num" style="width:76px;padding:8px 10px" title="Hoteles que permite su plan">
                                <label class="visually-hidden" for="estado-{{ $e->id }}">Estado de {{ $e->nombre }}</label>
                                <select id="estado-{{ $e->id }}" name="estado" class="input" style="width:auto;padding:8px 10px;color:{{ $e->estado === 'activa' ? 'var(--sage)' : 'var(--terra)' }};font-weight:700">
                                    <option value="activa" @selected($e->estado === 'activa')>Activa</option>
                                    <option value="suspendida" @selected($e->estado === 'suspendida')>Suspendida</option>
                                </select>
                                <button class="btn-hv btn-ghost btn-sm" aria-label="Guardar plan y estado de {{ $e->nombre }}"><i class="fas fa-check"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Aún no hay empresas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
