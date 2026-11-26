@extends('layouts.app')

@section('title', $esYo ? 'Mis datos' : 'Editar a ' . $usuario->name)

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Personal</span>
        <h1 class="page-title">{{ $esYo ? 'Mis' : 'Editar' }} <em>{{ $esYo ? 'datos' : $usuario->name }}</em></h1>
        <p class="page-lead">{{ $esYo ? 'Tu rol y tus hoteles los cambia otro administrador.' : 'Deja la contraseña vacía para no cambiarla.' }}</p>
    </div>
    <a href="{{ route('personal.index') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

<form method="POST" action="{{ route('personal.update', $usuario) }}" class="panel panel-pad reveal d1" style="max-width:760px" novalidate>
    @csrf
    @method('PUT')
    <div class="grid grid-2">
        @foreach ([
            ['name', 'Nombre completo', 'text', 'fa-user'],
            ['email', 'Correo electrónico', 'email', 'fa-envelope'],
            ['dni', 'DNI', 'text', 'fa-id-card'],
        ] as [$campo, $label, $tipo, $icono])
            <div class="field m-0" @if($campo === 'name') style="grid-column:1/-1" @endif>
                <label for="{{ $campo }}" class="field-label">{{ $label }}<span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas {{ $icono }}"></i>
                    <input id="{{ $campo }}" type="{{ $tipo }}" name="{{ $campo }}" value="{{ old($campo, $usuario->{$campo}) }}"
                           class="input @error($campo) is-invalid @enderror" @if($campo === 'dni') maxlength="8" inputmode="numeric" @endif required>
                </div>
                @error($campo)<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>
        @endforeach

        @unless ($esYo)
            <div class="field m-0" style="grid-column:1/-1">
                <span class="field-label">Rol<span class="req">*</span></span>
                <div class="methods">
                    @foreach ($roles as $codigo => $nombre)
                        <label class="method">
                            <input type="radio" name="rol" value="{{ $codigo }}" @checked(old('rol', $usuario->rol) === $codigo)>
                            <span><i class="fas {{ ['admin_empresa' => 'fa-crown', 'gerente' => 'fa-user-tie', 'recepcionista' => 'fa-bell-concierge'][$codigo] }}"></i>{{ $nombre }}</span>
                        </label>
                    @endforeach
                </div>
                @error('rol')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>

            <div class="field m-0" style="grid-column:1/-1" id="campoHoteles">
                <span class="field-label">Hoteles donde trabaja<span class="req">*</span></span>
                <div class="methods">
                    @foreach ($hoteles as $h)
                        <label class="method">
                            <input type="checkbox" name="hoteles[]" value="{{ $h->id }}" @checked(in_array($h->id, old('hoteles', $usuario->hoteles->pluck('id')->all())))>
                            <span><i class="fas fa-hotel"></i>{{ $h->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                @error('hoteles')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                @error('hoteles.*')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>
        @endunless

        <div class="field m-0">
            <label for="password" class="field-label">Nueva contraseña</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" type="password" name="password" class="input @error('password') is-invalid @enderror" placeholder="Sin cambios" autocomplete="new-password">
            </div>
            @error('password')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>
        <div class="field m-0">
            <label for="password-confirm" class="field-label">Confirmar contraseña</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password-confirm" type="password" name="password_confirmation" class="input" autocomplete="new-password">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end mt-4 pt-2">
        <a href="{{ route('personal.index') }}" class="btn-hv btn-ghost">Cancelar</a>
        <button type="submit" class="btn-hv"><i class="fas fa-floppy-disk"></i>Guardar cambios</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hoteles = document.getElementById('campoHoteles');
    if (!hoteles) return;
    const actualizar = () => hoteles.hidden = document.querySelector('input[name="rol"]:checked')?.value === 'admin_empresa';
    document.querySelectorAll('input[name="rol"]').forEach(r => r.addEventListener('change', actualizar));
    actualizar();
});
</script>
@endsection
