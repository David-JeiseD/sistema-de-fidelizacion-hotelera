@extends('layouts.app')

@section('title', 'Nuevo hotel')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Hoteles</span>
        <h1 class="page-title">Nuevo <em>hotel</em></h1>
        <p class="page-lead">Se suma a tu club: los huéspedes podrán ganar y canjear puntos también aquí.</p>
    </div>
    <a href="{{ route('hoteles.index') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

<form method="POST" action="{{ route('hoteles.store') }}" class="panel panel-pad reveal d1" style="max-width:680px" novalidate>
    @csrf
    <div class="grid grid-2">
        <div class="field m-0" style="grid-column:1/-1">
            <label for="nombre" class="field-label">Nombre del hotel<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-hotel"></i>
                <input id="nombre" name="nombre" maxlength="100" value="{{ old('nombre') }}" class="input @error('nombre') is-invalid @enderror" placeholder="Ej. Buena Vida Cusco" required autofocus>
            </div>
            @error('nombre')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>
        <div class="field m-0">
            <label for="ciudad" class="field-label">Ciudad</label>
            <div class="input-wrap"><i class="fas fa-city"></i><input id="ciudad" name="ciudad" maxlength="100" value="{{ old('ciudad') }}" class="input" placeholder="Cusco"></div>
        </div>
        <div class="field m-0">
            <label for="zona_horaria" class="field-label">Zona horaria<span class="req">*</span></label>
            <select id="zona_horaria" name="zona_horaria" class="input @error('zona_horaria') is-invalid @enderror">
                @foreach (['America/Lima' => 'Perú (Lima)', 'America/Bogota' => 'Colombia (Bogotá)', 'America/Guayaquil' => 'Ecuador', 'America/La_Paz' => 'Bolivia', 'America/Santiago' => 'Chile', 'America/Argentina/Buenos_Aires' => 'Argentina', 'America/Mexico_City' => 'México'] as $tz => $etiqueta)
                    <option value="{{ $tz }}" @selected(old('zona_horaria', $hotel->zona_horaria ?? 'America/Lima') === $tz)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('zona_horaria')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>
        <div class="field m-0" style="grid-column:1/-1">
            <label for="direccion" class="field-label">Dirección</label>
            <div class="input-wrap"><i class="fas fa-location-dot"></i><input id="direccion" name="direccion" maxlength="200" value="{{ old('direccion') }}" class="input" placeholder="Av. El Sol 123"></div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3" style="border-top:1px dashed var(--line)">
        <label class="switch">
            <input type="checkbox" name="copiar_habitaciones" value="1" @checked(old('copiar_habitaciones', true))>
            <span class="track"></span>
            Copiar los tipos de habitación de {{ $hotel->nombre }}
        </label>
        <button type="submit" class="btn-hv"><i class="fas fa-plus"></i>Crear hotel</button>
    </div>
</form>
@endsection
