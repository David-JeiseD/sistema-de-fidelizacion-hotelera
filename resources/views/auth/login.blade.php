@extends('layouts.app')

@section('title', 'Acceso del personal')

@section('content')
<div class="auth">
    @include('partials.auth-art')

    <section class="auth-form">
        <div class="auth-box reveal d1">
            <span class="eyebrow">Acceso del personal</span>
            <h2>Bienvenido de vuelta</h2>
            <p class="lead">Ingresa con tu correo, DNI y contraseña de trabajador.</p>

            @if (session('status'))
                <div class="alert-hv ok"><i class="fas fa-check"></i>{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="field">
                    <label for="email" class="field-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="input @error('email') is-invalid @enderror"
                               placeholder="nombre@buenavida.pe" required autocomplete="email" autofocus>
                    </div>
                    @error('email')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="dni" class="field-label">DNI</label>
                    <div class="input-wrap">
                        <i class="fas fa-id-card"></i>
                        <input id="dni" type="text" name="dni" value="{{ old('dni') }}"
                               class="input num @error('dni') is-invalid @enderror"
                               placeholder="8 dígitos" inputmode="numeric" maxlength="8" required autocomplete="off">
                    </div>
                    @error('dni')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password" class="field-label">Contraseña</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input id="password" type="password" name="password"
                               class="input @error('password') is-invalid @enderror"
                               placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="input-addon" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
                </div>

                <div class="auth-row">
                    <label class="check">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Recordarme
                    </label>
                    {{-- Sin correo configurado (MAIL_MAILER=log) el enlace no llegaría: la contraseña la cambia un administrador desde Personal --}}
                    @if (Route::has('password.request') && config('mail.default') !== 'log')
                        <a class="link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                    @else
                        <span class="subtle" style="font-size:12.5px">¿Olvidaste tu contraseña? Pídele al administrador que la cambie.</span>
                    @endif
                </div>

                <button type="submit" class="btn-hv btn-lg btn-block" id="loginBtn">
                    Ingresar <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const input = document.getElementById('password');
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    this.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    this.querySelector('i').className = 'fas ' + (visible ? 'fa-eye' : 'fa-eye-slash');
});
document.getElementById('dni').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});
</script>
@endsection
