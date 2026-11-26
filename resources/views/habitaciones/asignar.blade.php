@extends('layouts.app')

@section('title', 'Asignar habitación')

@php
    $iconoHabitacion = function ($nombre) {
        $n = mb_strtolower($nombre);
        return match (true) {
            str_contains($n, 'premium') => 'fa-crown',
            str_contains($n, 'matrimonial') => 'fa-heart',
            str_contains($n, 'doble') => 'fa-user-group',
            str_contains($n, 'personal'), str_contains($n, 'simple') => 'fa-user',
            default => 'fa-bed',
        };
    };
    $dniInicial = old('dni', request('dni'));
@endphp

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Recepción</span>
        <h1 class="page-title">Asignar <em>habitación</em></h1>
        <p class="page-lead">Registra la estadía del huésped. Los puntos de la habitación se suman a su libreta al confirmar.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver al panel</a>
</header>

<form method="POST" action="{{ route('habitaciones.asignar') }}" id="asignarForm" class="split" novalidate>
    @csrf
    <div class="stack">
        <section class="panel panel-pad reveal d1">
            <h2 class="panel-title mb-3"><span class="subtle serif me-2">1.</span>Huésped</h2>
            <div class="field">
                <label for="dni" class="field-label">DNI del huésped<span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="dni" id="dni" list="dnis" value="{{ $dniInicial }}" maxlength="8" inputmode="numeric"
                           class="input input-lg num @error('dni') is-invalid @enderror" placeholder="Escribe o elige un DNI" autocomplete="off" required>
                    <datalist id="dnis">
                        @foreach ($clientes as $c)
                            <option value="{{ $c->dni }}">{{ $c->nombre }}</option>
                        @endforeach
                    </datalist>
                </div>
                @error('dni')
                    <span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>
                @else
                    <span class="field-hint" id="guestHint" aria-live="polite">Empieza a escribir para buscar entre {{ $clientes->count() }} huéspedes.</span>
                @enderror
            </div>
        </section>

        <section class="panel panel-pad reveal d2">
            <div class="row-between mb-3">
                <h2 class="panel-title"><span class="subtle serif me-2">2.</span>Habitación</h2>
                @error('habitacion_id')<span class="field-error"><i class="fas fa-circle-exclamation"></i>Elige una habitación</span>@enderror
            </div>
            <fieldset class="rooms">
                <legend class="visually-hidden">Tipo de habitación</legend>
                @foreach ($habitaciones as $h)
                    <label class="room">
                        <input type="radio" name="habitacion_id" value="{{ $h->id }}" data-nombre="{{ $h->nombre }}" data-puntos="{{ $h->puntos }}"
                               {{ old('habitacion_id') == $h->id ? 'checked' : '' }} required>
                        <span class="room-body">
                            <span class="room-icon"><i class="fas {{ $iconoHabitacion($h->nombre) }}"></i></span>
                            <span>
                                <span class="room-name d-block">{{ $h->nombre }}</span>
                                <span class="room-pts d-block">+{{ $h->puntos }} puntos</span>
                            </span>
                            <span class="room-check"><i class="fas fa-check"></i></span>
                        </span>
                    </label>
                @endforeach
            </fieldset>
        </section>
    </div>

    <aside class="panel pass sticky reveal d3" aria-label="Resumen de la estadía">
        <div class="pass-head">
            <span class="eyebrow">Pase de estadía</span>
            <h2 class="panel-title mt-2" id="passGuest">Huésped por confirmar</h2>
        </div>
        <div class="pass-body">
            <div class="pass-row"><span>DNI</span><b class="num" id="passDni">—</b></div>
            <div class="pass-row"><span>Habitación</span><b id="passRoom">—</b></div>
            <div class="pass-row"><span>Fecha</span><b id="passDate">{{ now()->format('d/m/Y') }}</b></div>
            <div class="pass-points">
                <b id="passPoints">+0</b>
                <small>puntos a otorgar</small>
            </div>
            <button type="submit" class="btn-hv btn-lg btn-block" id="asignarBtn" disabled>
                <i class="fas fa-key"></i>Confirmar estadía
            </button>
            <p class="subtle text-center" style="font-size:12.5px">Una estadía por huésped al día.</p>
        </div>
    </aside>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const huespedes = @json($clientes->pluck('nombre', 'dni'));
    const dni = document.getElementById('dni');
    const hint = document.getElementById('guestHint');
    const btn = document.getElementById('asignarBtn');
    const $ = id => document.getElementById(id);
    document.getElementById('passDate').textContent = new Date().toLocaleDateString('es-PE');

    function actualizar() {
        dni.value = dni.value.replace(/\D/g, '').slice(0, 8);
        const nombre = huespedes[dni.value];
        const room = document.querySelector('input[name="habitacion_id"]:checked');

        $('passDni').textContent = dni.value || '—';
        $('passGuest').textContent = nombre || 'Huésped por confirmar';
        dni.classList.toggle('is-valid', !!nombre);
        dni.classList.toggle('is-invalid', dni.value.length === 8 && !nombre);
        if (hint) {
            hint.className = 'field-hint' + (nombre ? ' guest-found' : dni.value.length === 8 ? ' guest-missing' : '');
            hint.textContent = nombre
                ? '✓ ' + nombre
                : dni.value.length === 8 ? 'No hay ningún huésped con ese DNI.' : 'Escribe los 8 dígitos del DNI.';
        }

        $('passRoom').textContent = room ? room.dataset.nombre : '—';
        const pts = $('passPoints'), nuevo = '+' + (room ? room.dataset.puntos : 0);
        if (pts.textContent !== nuevo) {
            pts.textContent = nuevo;
            pts.classList.add('bump');
            setTimeout(() => pts.classList.remove('bump'), 250);
        }
        btn.disabled = !(nombre && room);
    }

    dni.addEventListener('input', actualizar);
    document.querySelectorAll('input[name="habitacion_id"]').forEach(r => r.addEventListener('change', actualizar));
    actualizar();

    document.getElementById('asignarForm').addEventListener('submit', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Registrando…';
    });
});
</script>
@endsection
