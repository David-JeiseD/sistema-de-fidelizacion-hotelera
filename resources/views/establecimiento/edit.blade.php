@extends('layouts.app')

@section('title', 'Establecimiento')

@php
    $secciones = [
        'Identidad' => [
            ['nombre', 'Nombre comercial', 'text', 'fa-hotel', 'Hotel Buena Vida', true, 'full'],
            ['eslogan', 'Eslogan', 'text', 'fa-feather', 'Club de huéspedes', false, 'full'],
            ['ruc', 'RUC', 'text', 'fa-file-invoice', '11 dígitos', false, ''],
        ],
        'Contacto' => [
            ['telefono', 'Teléfono', 'tel', 'fa-phone', '+51 984 123 456', false, ''],
            ['email', 'Correo', 'email', 'fa-envelope', 'reservas@buenavida.pe', false, ''],
            ['sitio_web', 'Sitio web', 'url', 'fa-globe', 'https://buenavida.pe', false, 'full'],
        ],
        'Ubicación' => [
            ['direccion', 'Dirección', 'text', 'fa-location-dot', 'Av. Principal 123', false, 'full'],
            ['ciudad', 'Ciudad', 'text', 'fa-city', 'Cusco', false, ''],
        ],
        'Horarios' => [
            ['check_in', 'Check-in', 'time', 'fa-door-open', '', false, ''],
            ['check_out', 'Check-out', 'time', 'fa-door-closed', '', false, ''],
        ],
    ];
    $valor = fn ($campo) => old($campo, in_array($campo, ['check_in', 'check_out']) ? $hotel->hora($campo) : $hotel->{$campo});
@endphp

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Administración</span>
        <h1 class="page-title">Datos del <em>establecimiento</em></h1>
        <p class="page-lead">El nombre y el eslogan aparecen en el menú, el acceso del personal y las tarjetas de los huéspedes.</p>
    </div>
    <span class="pill num" title="Última actualización">
        <i class="fas fa-clock-rotate-left"></i>
        Actualizado {{ $hotel->updated_at?->locale('es')->diffForHumans() ?? '—' }}
    </span>
</header>

<form method="POST" action="{{ route('establecimiento.update') }}" class="split" id="hotelForm" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    <div class="stack">
        @foreach ($secciones as $titulo => $campos)
            <section class="panel panel-pad reveal d{{ min($loop->iteration, 4) }}">
                <h2 class="panel-title mb-3"><span class="subtle serif me-2">{{ $loop->iteration }}.</span>{{ $titulo }}</h2>
                @if ($loop->first)
                    <div class="uploader mb-4">
                        <span class="uploader-thumb round"><img src="{{ $hotel->logo_url }}" alt="Logo actual" id="logoPreview"></span>
                        <div style="min-width:0">
                            <label class="field-label d-block" for="logo">Logo</label>
                            <p class="field-hint mb-2">JPG, PNG o WEBP de hasta 2 MB. Se ve mejor cuadrado.</p>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <label class="btn-hv btn-ghost btn-sm mb-0" for="logo"><i class="fas fa-upload"></i>Subir logo</label>
                                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" class="visually-hidden">
                                @if ($hotel->logo_path)
                                    <label class="check" style="font-size:13px"><input type="checkbox" name="quitar_logo" value="1" id="quitarLogo"> Volver al logo original</label>
                                @endif
                            </div>
                            @error('logo')<span class="field-error mt-2"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                        </div>
                    </div>
                @endif
                <div class="grid grid-2" style="gap:18px">
                    @foreach ($campos as [$campo, $label, $tipo, $icono, $ph, $req, $ancho])
                        <div class="field m-0" @if($ancho === 'full') style="grid-column:1/-1" @endif>
                            <label for="{{ $campo }}" class="field-label">{{ $label }}@if($req)<span class="req">*</span>@endif</label>
                            <div class="input-wrap">
                                <i class="fas {{ $icono }}"></i>
                                <input type="{{ $tipo }}" id="{{ $campo }}" name="{{ $campo }}" value="{{ $valor($campo) }}" placeholder="{{ $ph }}"
                                       class="input @error($campo) is-invalid @enderror {{ in_array($campo, ['ruc', 'telefono']) ? 'num' : '' }}"
                                       data-preview="{{ $campo }}"
                                       @if($campo === 'ruc') maxlength="11" inputmode="numeric" @endif
                                       @if($req) required @endif>
                            </div>
                            @error($campo)<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <aside class="stack sticky reveal d2">
        <span class="eyebrow">Vista previa</span>
        <div class="stub">
            @include('partials.rings', ['class' => 'hero-rings'])
            <div class="d-flex align-items-center gap-3 position-relative">
                <span class="brand-seal"><img src="{{ $hotel->logo_url }}" alt="" data-out-logo></span>
                <div style="min-width:0">
                    <h2 class="text-truncate" data-out="nombre">{{ $hotel->nombre }}</h2>
                    <div class="brand-sub" data-out="eslogan">{{ $hotel->eslogan }}</div>
                </div>
            </div>
            <div class="stub-rows position-relative">
                @foreach ([
                    ['direccion', 'fa-location-dot', 'Dirección'],
                    ['telefono', 'fa-phone', 'Teléfono'],
                    ['email', 'fa-envelope', 'Correo'],
                    ['sitio_web', 'fa-globe', 'Web'],
                    ['ruc', 'fa-file-invoice', 'RUC'],
                ] as [$campo, $icono, $label])
                    <div class="stub-row">
                        <span><i class="fas {{ $icono }} me-2"></i>{{ $label }}</span>
                        <b class="text-end text-truncate" style="font-family:var(--sans);font-size:14px;max-width:62%" data-out="{{ $campo }}">{{ $hotel->{$campo} ?: '—' }}</b>
                    </div>
                @endforeach
                <div class="stub-row total">
                    <span><i class="fas fa-door-open me-2"></i>Check-in / out</span>
                    <b class="num"><span data-out="check_in">{{ $hotel->hora('check_in') ?: '—' }}</span> · <span data-out="check_out">{{ $hotel->hora('check_out') ?: '—' }}</span></b>
                </div>
            </div>
            <p class="position-relative" style="font-size:12.5px;color:rgba(243,234,214,.6)" data-out="ciudad-linea">{{ $hotel->ciudad ? 'Te esperamos en ' . $hotel->ciudad . '.' : '' }}</p>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost">Cancelar</a>
            <button type="submit" class="btn-hv" id="guardarBtn"><i class="fas fa-floppy-disk"></i>Guardar cambios</button>
        </div>
    </aside>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const out = campo => document.querySelector('[data-out="' + campo + '"]');
    document.querySelectorAll('[data-preview]').forEach(input => {
        input.addEventListener('input', function () {
            if (this.id === 'ruc') this.value = this.value.replace(/\D/g, '').slice(0, 11);
            const v = this.value.trim();
            if (this.id === 'ciudad') {
                out('ciudad-linea').textContent = v ? 'Te esperamos en ' + v + '.' : '';
            } else if (out(this.id)) {
                out(this.id).textContent = v || (this.id === 'nombre' ? 'Nombre del hotel' : this.id === 'eslogan' ? '' : '—');
            }
        });
    });
    // Vista previa del logo antes de guardarlo
    document.getElementById('logo').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { showToast('El logo no puede pesar más de 2 MB.', 'warning', 'Archivo muy grande'); this.value = ''; return; }
        const url = URL.createObjectURL(file);
        document.getElementById('logoPreview').src = url;
        document.querySelector('[data-out-logo]').src = url;
        const quitar = document.getElementById('quitarLogo');
        if (quitar) quitar.checked = false;
    });
    document.getElementById('hotelForm').addEventListener('submit', function () {
        const btn = document.getElementById('guardarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Guardando…';
    });
});
</script>
@endsection
