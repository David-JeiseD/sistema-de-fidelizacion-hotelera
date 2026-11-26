@extends('layouts.app')

@php $editando = $metodo->exists; @endphp

@section('title', $editando ? 'Editar ' . $metodo->nombre : 'Nuevo método de pago')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Métodos de pago</span>
        <h1 class="page-title">{{ $editando ? 'Editar' : 'Nuevo' }} <em>método</em></h1>
        <p class="page-lead">El huésped verá el QR, el número y el titular en la pantalla de cobro.</p>
    </div>
    <a href="{{ route('metodos-pago.index') }}" class="btn-hv btn-ghost"><i class="fas fa-arrow-left"></i>Volver</a>
</header>

<form method="POST" enctype="multipart/form-data" novalidate class="split" id="metodoForm"
      action="{{ $editando ? route('metodos-pago.update', $metodo) : route('metodos-pago.store') }}">
    @csrf
    @if ($editando) @method('PUT') @endif

    <section class="panel panel-pad reveal d1">
        <div class="field">
            <label for="nombre" class="field-label">Nombre del método<span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fas fa-wallet"></i>
                <input id="nombre" name="nombre" list="sugerencias" maxlength="50" value="{{ old('nombre', $metodo->nombre) }}"
                       class="input @error('nombre') is-invalid @enderror" placeholder="Ej. Yape, Plin, Transferencia BCP" required autofocus>
                <datalist id="sugerencias">
                    @foreach (['Yape', 'Plin', 'Tunki', 'Transferencia BCP', 'Transferencia Interbank', 'Transferencia BBVA'] as $s)
                        <option value="{{ $s }}">
                    @endforeach
                </datalist>
            </div>
            @error('nombre')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>

        <div class="grid grid-2 mt-3" style="gap:18px">
            <div class="field m-0">
                <label for="numero" class="field-label">Número o cuenta<span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fas fa-mobile-screen"></i>
                    <input id="numero" name="numero" maxlength="20" inputmode="tel" value="{{ old('numero', $metodo->numero) }}"
                           class="input num @error('numero') is-invalid @enderror" placeholder="984 123 456" required>
                </div>
                @error('numero')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>
            <div class="field m-0">
                <label for="titular" class="field-label">Titular</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input id="titular" name="titular" maxlength="100" value="{{ old('titular', $metodo->titular) }}"
                           class="input @error('titular') is-invalid @enderror" placeholder="Nombre que verá el huésped">
                </div>
                @error('titular')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="field mt-4">
            <span class="field-label">Código QR @unless($editando)<span class="req">*</span>@endunless</span>
            <label class="dropzone @error('qr') is-invalid @enderror" id="dropzone">
                <input type="file" name="qr" id="qr" accept="image/png,image/jpeg,image/webp" @unless($editando) required @endunless>
                @if ($editando)
                    <img src="{{ $metodo->qr_url }}" alt="QR actual" id="qrThumb">
                    <span style="font-size:13px"><b>Cambiar imagen</b> · arrastra otra o haz clic</span>
                @else
                    <span class="dz-icon" id="dzIcon"><i class="fas fa-qrcode"></i></span>
                    <img src="" alt="Vista previa del QR" id="qrThumb" hidden>
                    <span style="font-size:13px" id="dzText"><b>Arrastra la imagen del QR</b> o haz clic para elegirla<br><span class="subtle">JPG, PNG o WEBP · hasta 2 MB</span></span>
                @endif
            </label>
            @error('qr')<span class="field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>@enderror
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3" style="border-top:1px dashed var(--line)">
            <label class="switch">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $metodo->activo) ? 'checked' : '' }}>
                <span class="track"></span>
                Mostrar en la pantalla de cobro
            </label>
            <button type="submit" class="btn-hv" id="guardarBtn"><i class="fas fa-floppy-disk"></i>{{ $editando ? 'Guardar cambios' : 'Agregar método' }}</button>
        </div>
    </section>

    <aside class="stack sticky reveal d2">
        <span class="eyebrow">Así lo verá el huésped</span>
        <div class="poster">
            @include('partials.rings', ['class' => 'hero-rings'])
            <div class="poster-method" data-out="nombre">{{ $metodo->nombre ?: 'Método de pago' }}</div>
            <div class="poster-qr">
                <img src="{{ $editando ? $metodo->qr_url : '' }}" alt="" id="posterQr" @unless($editando) style="opacity:.15" @endunless>
            </div>
            <div class="poster-num"><b data-out="numero">{{ $metodo->numero ?: '— — —' }}</b></div>
            <div class="poster-holder" data-out="titular">{{ $metodo->titular }}</div>
        </div>
    </aside>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const vacio = { nombre: 'Método de pago', numero: '— — —', titular: '' };
    ['nombre', 'numero', 'titular'].forEach(id => {
        document.getElementById(id).addEventListener('input', function () {
            document.querySelector('[data-out="' + id + '"]').textContent = this.value.trim() || vacio[id];
        });
    });

    const dz = document.getElementById('dropzone');
    const input = document.getElementById('qr');
    const thumb = document.getElementById('qrThumb');
    const poster = document.getElementById('posterQr');

    // Placeholder del póster: un cuadro gris en vez de una imagen rota
    if (!poster.getAttribute('src')) poster.src = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="#ccc"/></svg>');

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (!/^image\/(png|jpeg|webp)$/.test(file.type)) { showToast('El QR debe ser JPG, PNG o WEBP.', 'warning', 'Formato no válido'); this.value = ''; return; }
        if (file.size > 2 * 1024 * 1024) { showToast('El QR no puede pesar más de 2 MB.', 'warning', 'Archivo muy grande'); this.value = ''; return; }
        const url = URL.createObjectURL(file);
        thumb.src = url; thumb.hidden = false;
        poster.src = url; poster.style.opacity = 1;
        const icon = document.getElementById('dzIcon'); if (icon) icon.hidden = true;
        const text = document.getElementById('dzText'); if (text) text.textContent = file.name + ' · clic para cambiarla';
    });
    ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, () => dz.classList.add('is-over')));
    ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, () => dz.classList.remove('is-over')));

    document.getElementById('metodoForm').addEventListener('submit', function () {
        const btn = document.getElementById('guardarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Guardando…';
    });
});
</script>
@endsection
