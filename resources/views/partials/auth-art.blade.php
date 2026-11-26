<section class="auth-art" aria-hidden="true">
    <div class="brand">
        <span class="brand-seal"><img src="{{ asset('images/logo.jpg') }}" alt=""></span>
        <span>
            <span class="brand-name d-block">{{ $hotel->nombre }}</span>
            <span class="brand-sub d-block">{{ $hotel->eslogan }}</span>
        </span>
    </div>

    <div class="auth-quote reveal">
        <span class="eyebrow">Programa de fidelidad</span>
        <h1 class="mt-3">Cada estadía <em>vuelve</em> como un recuerdo.</h1>
        <p>Registra huéspedes, asigna habitaciones y convierte cada noche en puntos que se canjean por pequeños placeres.</p>
    </div>

    @include('partials.rings', ['class' => 'auth-rings'])

    <div class="auth-foot">
        <span>Recepción</span><span>·</span><span>Uso interno</span>
    </div>
</section>
