@extends('layouts.app')

@section('title', 'Registrar huésped')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Nuevo miembro</span>
        <h1 class="page-title">Registrar <em>huésped</em></h1>
        <p class="page-lead">Su tarjeta del club se emite al instante y empieza a sumar puntos desde la primera estadía.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver al panel</a>
</header>

<div class="split">
    <form method="POST" action="{{ route('clientes.store') }}" id="registrarForm" class="panel panel-pad reveal d1" novalidate>
        @csrf
        <h2 class="panel-title mb-1">Datos del huésped</h2>
        <p class="panel-note mb-4">Los campos marcados con <span style="color:var(--terra)">*</span> son obligatorios.</p>

        <div class="field">
            <label for="nombre" class="field-label">Nombre completo<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100"
                       class="input @error('nombre') is-invalid @enderror" placeholder="Ej. María Fernanda Quispe" required autofocus>
            </div>
            @error('nombre')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="dni" class="field-label">DNI<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-id-card"></i>
                <input type="text" id="dni" name="dni" value="{{ old('dni') }}" maxlength="8" inputmode="numeric" pattern="[0-9]{8}"
                       class="input num @error('dni') is-invalid @enderror" placeholder="8 dígitos" required>
            </div>
            @error('dni')
                <span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>
            @else
                <span class="field-hint" id="dniHint">Documento Nacional de Identidad, 8 dígitos.</span>
            @enderror
        </div>

        <div class="note mt-4">
            <i class="fas fa-lightbulb"></i>
            <span>Tras registrarlo, asígnale su primera habitación para que empiece a acumular puntos.</span>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4">
            <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost">Cancelar</a>
            <button type="submit" class="btn-hv" id="registrarBtn"><i class="fas fa-user-plus"></i>Registrar huésped</button>
        </div>
    </form>

    <aside class="stack sticky reveal d2">
        <div>
            <span class="eyebrow mb-3">Vista previa</span>
            <div class="mt-3">
                @include('partials.member-card', [
                    'nombre' => old('nombre') ?: 'Nombre del huésped',
                    'dni' => old('dni') ?: '— — — —',
                    'puntos' => 0,
                    'desde' => now()->locale('es')->isoFormat('MM/YYYY'),
                ])
            </div>
        </div>
        <p class="subtle text-center" style="font-size:13px">La tarjeta se actualiza mientras escribes.</p>
    </aside>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nombre = document.getElementById('nombre');
    const dni = document.getElementById('dni');
    const outNombre = document.querySelector('[data-mc="nombre"]');
    const outDni = document.querySelector('[data-mc="dni"]');

    nombre.addEventListener('input', () => outNombre.textContent = nombre.value.trim() || 'Nombre del huésped');
    dni.addEventListener('input', () => {
        dni.value = dni.value.replace(/\D/g, '').slice(0, 8);
        outDni.textContent = dni.value || '— — — —';
        dni.classList.toggle('is-valid', dni.value.length === 8);
    });

    document.getElementById('registrarForm').addEventListener('submit', function (e) {
        if (!nombre.value.trim() || !/^\d{8}$/.test(dni.value)) {
            e.preventDefault();
            nombre.classList.toggle('is-invalid', !nombre.value.trim());
            dni.classList.toggle('is-invalid', !/^\d{8}$/.test(dni.value));
            showToast('Completa el nombre y un DNI de 8 dígitos.', 'warning', 'Revisa el formulario');
            return;
        }
        const btn = document.getElementById('registrarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Registrando…';
    });
});
</script>
@endsection
