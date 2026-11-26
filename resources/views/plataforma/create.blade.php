@extends('layouts.app')

@section('title', 'Nueva empresa')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Plataforma</span>
        <h1 class="page-title">Nueva <em>empresa</em></h1>
        <p class="page-lead">Da de alta a un cliente con su primer hotel y su administrador. Él completará el resto: logo, QR, habitaciones, recompensas y personal.</p>
    </div>
    <a href="{{ route('plataforma') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

@php
    $campo = function ($nombre, $label, $icono, $extra = []) {
        return compact('nombre', 'label', 'icono') + $extra + ['tipo' => 'text', 'req' => true, 'ph' => '', 'full' => false];
    };
    $secciones = [
        'Empresa' => [
            $campo('empresa', 'Nombre comercial', 'fa-building', ['ph' => 'Ej. Hoteles Sol del Sur', 'full' => true]),
            $campo('ruc', 'RUC', 'fa-file-invoice', ['req' => false, 'ph' => '11 dígitos']),
            $campo('max_hoteles', 'Hoteles en su plan', 'fa-layer-group', ['tipo' => 'number', 'ph' => '1']),
        ],
        'Primer hotel' => [
            $campo('hotel', 'Nombre del hotel', 'fa-hotel', ['ph' => 'Ej. Sol del Sur Arequipa']),
            $campo('ciudad', 'Ciudad', 'fa-city', ['req' => false, 'ph' => 'Arequipa']),
        ],
        'Administrador de la empresa' => [
            $campo('admin_nombre', 'Nombre completo', 'fa-user', ['full' => true]),
            $campo('admin_email', 'Correo', 'fa-envelope', ['tipo' => 'email']),
            $campo('admin_dni', 'DNI', 'fa-id-card', ['ph' => '8 dígitos']),
            $campo('admin_password', 'Contraseña inicial', 'fa-lock', ['tipo' => 'password', 'ph' => 'Mínimo 8 caracteres']),
            $campo('admin_password_confirmation', 'Confirmar contraseña', 'fa-lock', ['tipo' => 'password']),
        ],
    ];
@endphp

<form method="POST" action="{{ route('plataforma.empresas.store') }}" class="stack" style="max-width:760px" novalidate>
    @csrf
    @foreach ($secciones as $titulo => $campos)
        <section class="panel panel-pad reveal d{{ $loop->iteration }}">
            <h2 class="panel-title mb-3"><span class="subtle serif me-2">{{ $loop->iteration }}.</span>{{ $titulo }}</h2>
            <div class="grid grid-2" style="gap:18px">
                @foreach ($campos as $c)
                    <div class="field m-0" @if($c['full']) style="grid-column:1/-1" @endif>
                        <label for="{{ $c['nombre'] }}" class="field-label">{{ $c['label'] }}@if($c['req'])<span class="req">*</span>@endif</label>
                        <div class="input-wrap">
                            <i class="fas {{ $c['icono'] }}"></i>
                            <input id="{{ $c['nombre'] }}" name="{{ $c['nombre'] }}" type="{{ $c['tipo'] }}" placeholder="{{ $c['ph'] }}"
                                   value="{{ $c['tipo'] === 'password' ? '' : old($c['nombre'], $c['nombre'] === 'max_hoteles' ? 1 : '') }}"
                                   class="input @error($c['nombre']) is-invalid @enderror" @if($c['tipo'] === 'number') min="1" @endif
                                   @if($c['tipo'] === 'password') autocomplete="new-password" @endif @if($c['req']) required @endif>
                        </div>
                        @error($c['nombre'])<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('plataforma') }}" class="btn-hv btn-ghost">Cancelar</a>
        <button type="submit" class="btn-hv"><i class="fas fa-building-circle-check"></i>Dar de alta</button>
    </div>
</form>
@endsection
