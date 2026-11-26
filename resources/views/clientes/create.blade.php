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
            <span class="field-label">Documento<span class="req">*</span></span>
            <div class="d-flex gap-2">
                <select name="tipo_documento" id="tipo_documento" class="input" style="max-width:190px" aria-label="Tipo de documento">
                    @foreach (\App\Models\Persona::TIPOS as $codigo => [$etiqueta])
                        <option value="{{ $codigo }}" @selected(old('tipo_documento', 'DNI') === $codigo)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <div class="input-wrap flex-grow-1">
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" maxlength="12"
                           class="input num @error('numero_documento') is-invalid @enderror" placeholder="Número" aria-label="Número de documento" required>
                </div>
            </div>
            @error('numero_documento')
                <span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>
            @else
                <span class="field-hint" id="docHint">8 dígitos.</span>
            @enderror
            @error('tipo_documento')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
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
                    'dni' => old('numero_documento') ?: '— — — —',
                    'tipo' => old('tipo_documento', 'DNI'),
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
    const tipo = document.getElementById('tipo_documento');
    const dni = document.getElementById('numero_documento');
    const hint = document.getElementById('docHint');
    const outNombre = document.querySelector('[data-mc="nombre"]');
    const outDni = document.querySelector('[data-mc="dni"]');
    const outTipo = document.querySelector('[data-mc="tipo"]');
    // Mismas reglas que el servidor (Persona::TIPOS)
    const reglas = {
        @foreach (\App\Models\Persona::TIPOS as $codigo => [$etiqueta, $patron, $texto])
            {{ $codigo }}: { re: {!! $patron !!}, texto: @json($texto), etiqueta: @json($etiqueta) },
        @endforeach
    };
    const valido = () => reglas[tipo.value].re.test(dni.value);

    function actualizar() {
        dni.value = (tipo.value === 'DNI' ? dni.value.replace(/\D/g, '') : dni.value.replace(/[^a-z0-9]/gi, '').toUpperCase()).slice(0, 12);
        outDni.textContent = dni.value || '— — — —';
        outTipo.textContent = tipo.value;
        dni.inputMode = tipo.value === 'DNI' ? 'numeric' : 'text';
        if (hint) hint.textContent = reglas[tipo.value].etiqueta + ': ' + reglas[tipo.value].texto + '.';
        dni.classList.toggle('is-valid', valido());
    }
    nombre.addEventListener('input', () => outNombre.textContent = nombre.value.trim() || 'Nombre del huésped');
    dni.addEventListener('input', actualizar);
    tipo.addEventListener('change', actualizar);
    actualizar();

    document.getElementById('registrarForm').addEventListener('submit', function (e) {
        if (!nombre.value.trim() || !valido()) {
            e.preventDefault();
            nombre.classList.toggle('is-invalid', !nombre.value.trim());
            dni.classList.toggle('is-invalid', !valido());
            showToast('Completa el nombre y un documento válido (' + reglas[tipo.value].etiqueta + ': ' + reglas[tipo.value].texto + ').', 'warning', 'Revisa el formulario');
            return;
        }
        const btn = document.getElementById('registrarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Registrando…';
    });
});
</script>
@endsection
