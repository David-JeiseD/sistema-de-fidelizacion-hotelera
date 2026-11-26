@extends('layouts.app')

@section('title', 'Nuevo trabajador')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Administración</span>
        <h1 class="page-title">Nuevo <em>trabajador</em></h1>
        <p class="page-lead">Crea el acceso de un miembro del personal. Entrará con su correo, DNI y contraseña.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver al panel</a>
</header>

<form method="POST" action="{{ url('/9ax1g9') }}" class="panel panel-pad reveal d1" style="max-width:760px" novalidate>
    @csrf
    <div class="grid grid-2">
        @foreach ([
            ['name', 'Nombre completo', 'text', 'fa-user', 'Ej. Rosa Huamán', 'name'],
            ['email', 'Correo electrónico', 'email', 'fa-envelope', 'nombre@buenavida.pe', 'email'],
            ['dni', 'DNI', 'text', 'fa-id-card', '8 dígitos', 'off'],
        ] as [$campo, $label, $tipo, $icono, $ph, $auto])
            <div class="field m-0 {{ $campo === 'name' ? 'grid-full' : '' }}" @if($campo === 'name') style="grid-column:1/-1" @endif>
                <label for="{{ $campo }}" class="field-label">{{ $label }}<span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas {{ $icono }}"></i>
                    <input id="{{ $campo }}" type="{{ $tipo }}" name="{{ $campo }}" value="{{ old($campo) }}" placeholder="{{ $ph }}" autocomplete="{{ $auto }}"
                           class="input @error($campo) is-invalid @enderror {{ $campo === 'dni' ? 'num' : '' }}"
                           @if($campo === 'dni') maxlength="8" inputmode="numeric" @endif required>
                </div>
                @error($campo)<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>
        @endforeach

        <div class="field m-0">
            <label for="password" class="field-label">Contraseña<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" type="password" name="password" class="input @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
            </div>
            @error('password')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>
        <div class="field m-0">
            <label for="password-confirm" class="field-label">Confirmar contraseña<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password-confirm" type="password" name="password_confirmation" class="input" placeholder="Repítela" autocomplete="new-password" required>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end mt-4 pt-2">
        <a href="{{ route('dashboard') }}" class="btn-hv btn-ghost">Cancelar</a>
        <button type="submit" class="btn-hv"><i class="fas fa-id-badge"></i>Crear acceso</button>
    </div>
</form>
@endsection
