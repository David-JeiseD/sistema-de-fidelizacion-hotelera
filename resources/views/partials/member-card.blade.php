{{-- Tarjeta de membresía. Variables: $nombre, $dni, $puntos, $desde (texto) --}}
<div class="card-stage">
    <div class="member-card" data-tilt role="img" aria-label="Tarjeta de membresía de {{ $nombre }}: {{ $puntos }} puntos">
        <img class="mc-watermark" src="{{ asset('images/logo.jpg') }}" alt="">
        <div class="mc-top">
            <div class="mc-brand">{{ $hotel->nombre_corto }}<small>{{ $hotel->eslogan ?: 'Club de huéspedes' }}</small></div>
            <span class="mc-chip"></span>
        </div>
        <div>
            <div class="mc-points">
                <span class="value foil" data-mc="puntos">{{ number_format($puntos, 0, ',', '.') }}</span>
                <span class="unit">puntos</span>
            </div>
        </div>
        <div class="mc-bottom">
            <div style="min-width:0">
                <div class="mc-name" data-mc="nombre">{{ $nombre }}</div>
                <div class="mc-meta">DNI <span data-mc="dni">{{ $dni }}</span></div>
            </div>
            <div class="mc-since">Miembro desde<b>{{ $desde }}</b></div>
        </div>
    </div>
</div>

@once
    @push('card-script')
    <script>
    // Inclinación 3D y brillo que siguen al puntero
    document.querySelectorAll('[data-tilt]').forEach(card => {
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        card.addEventListener('pointermove', e => {
            const r = card.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            card.classList.add('is-tilting');
            card.style.setProperty('--ry', ((x - .5) * 14) + 'deg');
            card.style.setProperty('--rx', ((.5 - y) * 10) + 'deg');
            card.style.setProperty('--mx', (x * 100) + '%');
            card.style.setProperty('--my', (y * 100) + '%');
        });
        card.addEventListener('pointerleave', () => {
            card.classList.remove('is-tilting');
            card.style.setProperty('--rx', '0deg');
            card.style.setProperty('--ry', '0deg');
        });
    });
    </script>
    @endpush
@endonce
