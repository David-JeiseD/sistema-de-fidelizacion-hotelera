@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="auth">
    @include('partials.auth-art')

    <section class="auth-form">
        <div class="auth-box reveal d1">
            <span class="eyebrow">Recuperar acceso</span>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p class="lead">Escribe tu correo de trabajador y te enviaremos un enlace para crear una nueva.</p>

            @if (session('status'))
                <div class="alert-hv ok"><i class="fas fa-check"></i>{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="email" class="field-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@buenavida.pe"
                               class="input @error('email') is-invalid @enderror" required autocomplete="email" autofocus>
                    </div>
                    @error('email')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn-hv btn-lg btn-block mt-4">Enviar enlace <i class="fas fa-paper-plane"></i></button>
            </form>
            <p class="text-center mt-4"><a class="link" href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Volver a ingresar</a></p>
        </div>
    </section>
</div>
@endsection
