@extends('layouts.app')

@php $editando = $tipo->exists; @endphp

@section('title', $editando ? 'Editar ' . $tipo->nombre : 'Nuevo tipo de habitación')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">{{ $hotel->nombre }}</span>
        <h1 class="page-title">{{ $editando ? 'Editar' : 'Nuevo' }} <em>tipo</em></h1>
        <p class="page-lead">Cambiar los puntos solo afecta a las estadías nuevas; las anteriores conservan lo que otorgaron.</p>
    </div>
    <a href="{{ route('tipos-habitacion.index') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

<form method="POST" action="{{ $editando ? route('tipos-habitacion.update', $tipo) : route('tipos-habitacion.store') }}" class="panel panel-pad reveal d1" style="max-width:620px" novalidate>
    @csrf
    @if ($editando) @method('PUT') @endif

    <div class="field">
        <label for="nombre" class="field-label">Nombre<span class="req">*</span></label>
        <div class="input-wrap">
            <i class="fas fa-bed"></i>
            <input id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $tipo->nombre) }}" class="input @error('nombre') is-invalid @enderror" placeholder="Ej. Matrimonial Premium" required autofocus>
        </div>
        @error('nombre')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="puntos" class="field-label">Puntos por estadía<span class="req">*</span></label>
        <div class="input-wrap">
            <i class="fas fa-star"></i>
            <input id="puntos" name="puntos" type="number" min="0" step="1" value="{{ old('puntos', $tipo->puntos) }}" class="input num @error('puntos') is-invalid @enderror" placeholder="Ej. 60" required>
        </div>
        @error('puntos')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3" style="border-top:1px dashed var(--line)">
        <label class="switch">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $tipo->activo ?? true) ? 'checked' : '' }}>
            <span class="track"></span>
            Disponible en recepción
        </label>
        <button type="submit" class="btn-hv"><i class="fas fa-floppy-disk"></i>{{ $editando ? 'Guardar cambios' : 'Agregar tipo' }}</button>
    </div>
</form>
@endsection
