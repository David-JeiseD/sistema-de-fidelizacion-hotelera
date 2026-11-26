@extends('layouts.app')

@section('title', 'Nueva contraseña')

@section('content')
<div class="auth">
    @include('partials.auth-art')

    <section class="auth-form">
        <div class="auth-box reveal d1">
            <span class="eyebrow">Recuperar acceso</span>
            <h2>Crea una nueva contraseña</h2>
            <p class="lead">Debe tener al menos 8 caracteres.</p>

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="field">
                    <label for="email" class="field-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}"
                               class="input @error('email') is-invalid @enderror" required autocomplete="email">
                    </div>
                    @error('email')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password" class="field-label">Nueva contraseña</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input id="password" type="password" name="password" class="input @error('password') is-invalid @enderror" required autocomplete="new-password" autofocus>
                    </div>
                    @error('password')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password-confirm" class="field-label">Confirmar contraseña</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input id="password-confirm" type="password" name="password_confirmation" class="input" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn-hv btn-lg btn-block mt-4">Guardar contraseña <i class="fas fa-arrow-right"></i></button>
            </form>
        </div>
    </section>
</div>
@endsection
