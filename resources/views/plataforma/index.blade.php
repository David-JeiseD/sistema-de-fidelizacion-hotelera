@extends('layouts.app')

@section('title', 'Plataforma')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Plataforma</span>
        <h1 class="page-title">Empresas <em>clientes</em></h1>
        <p class="page-lead">Todas las cadenas y hoteles que usan el sistema. Pronto podrás darlos de alta, cambiar su plan y suspenderlos desde aquí.</p>
    </div>
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
        <table class="ledger">
            <thead>
                <tr>
                    <th scope="col">Empresa</th>
                    <th scope="col" class="t-right">Hoteles</th>
                    <th scope="col" class="t-right">Miembros</th>
                    <th scope="col" class="t-right">Personal</th>
                    <th scope="col">Estado</th>
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
                        <td class="t-right num">{{ $e->hoteles_count }} <span class="subtle">/ {{ $e->max_hoteles }}</span></td>
                        <td class="t-right num">{{ $e->clientes_count }}</td>
                        <td class="t-right num">{{ $e->usuarios_count }}</td>
                        <td><span class="pill {{ $e->estado === 'activa' ? 'pill-plus' : 'pill-minus' }}">{{ ucfirst($e->estado) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Aún no hay empresas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
