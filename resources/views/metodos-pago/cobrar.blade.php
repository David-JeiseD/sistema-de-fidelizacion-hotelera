@extends('layouts.app')

@section('title', 'Cobrar con QR')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Recepción</span>
        <h1 class="page-title">Cobrar con <em>QR</em></h1>
        <p class="page-lead">Elige el método, escribe el monto si quieres y muéstrale la pantalla al huésped para que pague desde su celular.</p>
    </div>
    <a href="{{ route('metodos-pago.index') }}" class="btn-hv btn-ghost"><i class="fas fa-wallet"></i>Métodos de pago</a>
</header>

@if ($metodos->isEmpty())
    <section class="panel empty reveal d1" style="padding:56px 24px">
        <i class="fas fa-qrcode" style="font-size:40px"></i>
        <h2 class="panel-title mt-2" style="color:var(--ink)">No hay métodos de pago activos</h2>
        <p class="mt-2 mb-4">Sube el QR de tu billetera o cuenta para empezar a cobrar.</p>
        <a href="{{ route('metodos-pago.create') }}" class="btn-hv"><i class="fas fa-plus"></i>Agregar método</a>
    </section>
@else
    <div class="split">
        <section class="panel panel-pad reveal d1">
            <h2 class="panel-title mb-3"><span class="subtle serif me-2">1.</span>Método</h2>
            <fieldset class="methods">
                <legend class="visually-hidden">Método de pago</legend>
                @foreach ($metodos as $m)
                    <label class="method">
                        <input type="radio" name="metodo" value="{{ $m->id }}" {{ $loop->first ? 'checked' : '' }}>
                        <span><i class="fas fa-qrcode"></i>{{ $m->nombre }}</span>
                    </label>
                @endforeach
            </fieldset>

            <h2 class="panel-title mt-4 mb-3"><span class="subtle serif me-2">2.</span>Detalle <span class="subtle" style="font-family:var(--sans);font-size:13px">(opcional)</span></h2>
            <div class="field">
                <label for="monto" class="field-label">Monto a cobrar</label>
                <div class="money-wrap">
                    <span>S/</span>
                    <input type="text" id="monto" inputmode="decimal" class="input num" placeholder="0.00" autocomplete="off">
                </div>
            </div>
            <div class="field">
                <label for="concepto" class="field-label">Concepto</label>
                <div class="input-wrap">
                    <i class="fas fa-receipt"></i>
                    <input type="text" id="concepto" maxlength="80" class="input" placeholder="Ej. Habitación Doble · 1 noche">
                </div>
            </div>

            <div class="note mt-4">
                <i class="fas fa-circle-info"></i>
                <span>El monto y el concepto solo se muestran en pantalla, no se guardan. Verifica el comprobante del huésped antes de entregar la llave.</span>
            </div>
        </section>

        <aside class="stack sticky reveal d2">
            <div class="poster" id="poster">
                @include('partials.rings', ['class' => 'hero-rings'])
                <button type="button" class="btn-hv btn-ghost btn-sm poster-exit" id="salirBtn" style="--fg:#f3ead6;--bd:rgba(243,234,214,.3)"><i class="fas fa-compress"></i>Salir</button>
                <div class="poster-brand">
                    <span class="brand-seal"><img src="{{ $hotel->logo_url }}" alt=""></span>
                    <b>{{ $hotel->nombre }}</b>
                </div>
                <div class="poster-method" id="pMetodo"></div>
                <div class="poster-qr"><img src="" alt="" id="pQr"></div>
                <div class="poster-amount" id="pMonto"></div>
                <div class="poster-concept" id="pConcepto"></div>
                <div class="poster-num">
                    <b id="pNumero"></b>
                    <button type="button" id="copiarBtn" title="Copiar número" aria-label="Copiar número"><i class="fas fa-copy"></i></button>
                </div>
                <div class="poster-holder" id="pTitular"></div>
                <p class="poster-foot">Escanea el código con tu app de pagos y muestra el comprobante en recepción.</p>
            </div>
            <button type="button" class="btn-hv btn-brass btn-lg btn-block" id="pantallaBtn"><i class="fas fa-expand"></i>Mostrar en pantalla completa</button>
        </aside>
    </div>
@endif
@endsection

@section('scripts')
@if ($metodos->isNotEmpty())
@php
    // @json no admite closures con corchetes; se prepara aquí.
    $datosMetodos = $metodos->mapWithKeys(fn ($m) => [$m->id => [
        'nombre' => $m->nombre, 'numero' => $m->numero, 'titular' => $m->titular, 'qr' => $m->qr_url,
    ]]);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const metodos = @json($datosMetodos);
    const $ = id => document.getElementById(id);
    const soles = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
    const poster = $('poster');

    function pintar() {
        const m = metodos[document.querySelector('input[name="metodo"]:checked').value];
        $('pMetodo').textContent = 'Paga con ' + m.nombre;
        $('pQr').src = m.qr;
        $('pQr').alt = 'Código QR de ' + m.nombre;
        $('pNumero').textContent = m.numero;
        $('pTitular').textContent = m.titular ? 'A nombre de ' + m.titular : '';
        const monto = parseFloat($('monto').value.replace(',', '.'));
        $('pMonto').textContent = monto > 0 ? soles.format(monto) : '';
        $('pConcepto').textContent = $('concepto').value.trim();
    }

    document.querySelectorAll('input[name="metodo"]').forEach(r => r.addEventListener('change', pintar));
    $('monto').addEventListener('input', function () {
        this.value = this.value.replace(/[^\d.,]/g, '').replace(/([.,]\d{2}).+$/, '$1');
        pintar();
    });
    $('concepto').addEventListener('input', pintar);
    pintar();

    $('copiarBtn').addEventListener('click', function () {
        navigator.clipboard?.writeText($('pNumero').textContent.replace(/\s/g, ''))
            .then(() => showToast('Número copiado al portapapeles.', 'success', 'Copiado'))
            .catch(() => showToast('No se pudo copiar el número.', 'warning', 'Atención'));
    });

    const overlay = on => { poster.classList.toggle('is-full', on); document.body.style.overflow = on ? 'hidden' : ''; };
    $('pantallaBtn').addEventListener('click', () => {
        const pedido = poster.requestFullscreen ? poster.requestFullscreen() : Promise.reject();
        pedido.catch(() => overlay(true));
        // Algunos navegadores embebidos dejan la petición pendiente sin rechazarla
        setTimeout(() => { if (!document.fullscreenElement) overlay(true); }, 500);
    });
    $('salirBtn').addEventListener('click', () => {
        if (document.fullscreenElement) document.exitFullscreen();
        overlay(false);
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') overlay(false); });
});
</script>
@endif
@endsection
