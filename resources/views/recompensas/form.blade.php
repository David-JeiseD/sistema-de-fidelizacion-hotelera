@extends('layouts.app')

@php $editando = $recompensa->exists; @endphp

@section('title', $editando ? 'Editar ' . $recompensa->nombre : 'Nueva recompensa')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Catálogo del club</span>
        <h1 class="page-title">{{ $editando ? 'Editar' : 'Nueva' }} <em>recompensa</em></h1>
        <p class="page-lead">Cambiar los puntos no afecta los canjes ya hechos.</p>
    </div>
    <a href="{{ route('recompensas.index') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

<form method="POST" action="{{ $editando ? route('recompensas.update', $recompensa) : route('recompensas.store') }}" class="panel panel-pad reveal d1" style="max-width:620px" novalidate>
    @csrf
    @if ($editando) @method('PUT') @endif

    <div class="field">
        <label for="nombre" class="field-label">Nombre<span class="req">*</span></label>
        <div class="input-wrap">
            <i class="fas fa-gift"></i>
            <input id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $recompensa->nombre) }}" class="input @error('nombre') is-invalid @enderror" placeholder="Ej. Desayuno buffet" required autofocus>
        </div>
        @error('nombre')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="puntos" class="field-label">Puntos necesarios<span class="req">*</span></label>
        <div class="input-wrap">
            <i class="fas fa-star"></i>
            <input id="puntos" name="puntos" type="number" min="1" step="1" value="{{ old('puntos', $recompensa->puntos) }}" class="input num @error('puntos') is-invalid @enderror" placeholder="Ej. 300" required>
        </div>
        @error('puntos')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="hotel_id" class="field-label">Dónde se puede canjear</label>
        <select id="hotel_id" name="hotel_id" class="input @error('hotel_id') is-invalid @enderror">
            <option value="">Todos los hoteles de la empresa</option>
            @foreach ($hoteles as $h)
                <option value="{{ $h->id }}" @selected((string) old('hotel_id', $recompensa->hotel_id) === (string) $h->id)>Solo en {{ $h->nombre }}</option>
            @endforeach
        </select>
        @error('hotel_id')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3" style="border-top:1px dashed var(--line)">
        <label class="switch">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $recompensa->activo ?? true) ? 'checked' : '' }}>
            <span class="track"></span>
            Disponible para canjear
        </label>
        <button type="submit" class="btn-hv"><i class="fas fa-floppy-disk"></i>{{ $editando ? 'Guardar cambios' : 'Agregar recompensa' }}</button>
    </div>
</form>
@endsection
