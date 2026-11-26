@extends('layouts.app')

@section('title', 'Libreta de puntos')

@section('content')
<header class="page-head reveal">
    <div>
        <span class="eyebrow">Libreta de puntos</span>
        <h1 class="page-title">Buscar <em>huésped</em></h1>
        <p class="page-lead">Abre la libreta de un miembro para ver su saldo, su historial y canjear recompensas.</p>
    </div>
    <a href="{{ route('clientes.create') }}" class="btn-hv btn-ghost"><i class="fas fa-user-plus"></i>Nuevo huésped</a>
</header>

<div class="split">
    <section class="panel reveal d1">
        <form method="POST" action="{{ route('clientes.puntos.consultar') }}" id="consultarForm" class="search-hero" novalidate>
            @csrf
            <label for="dni" class="field-label d-block mb-2">DNI o nombre</label>
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-wrap flex-grow-1">
                    <i class="fas fa-magnifying-glass"></i>
                    <input name="dni" id="dni" value="{{ old('dni', request('dni')) }}" autocomplete="off" required
                           class="input input-lg @error('dni') is-invalid @enderror" placeholder="Ej. 76799754 o “María”">
                </div>
                <button type="submit" class="btn-hv btn-lg" id="consultarBtn">Abrir libreta</button>
            </div>
            @error('dni')
                <span class="field-error mt-2"><i class="fas fa-circle-exclamation"></i>{{ $message }}</span>
            @else
                <span class="field-hint d-block mt-2" id="searchHint" aria-live="polite">Filtra la lista mientras escribes o pulsa un huésped para abrir su libreta.</span>
            @enderror
        </form>

        <div class="panel-head" style="border-top:1px solid var(--line)">
            <h2 class="panel-title" style="font-size:17px">Directorio</h2>
            <span class="pill num" id="countPill">{{ $clientes->count() }} huéspedes</span>
        </div>
        @if ($clientes->isEmpty())
            <div class="empty"><i class="fas fa-users"></i>Aún no hay huéspedes registrados.</div>
        @else
            <ul class="directory" id="directory">
                @foreach ($clientes->sortBy('nombre') as $c)
                    @php $ini = collect(explode(' ', trim($c->nombre)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join(''); @endphp
                    <li data-search="{{ mb_strtolower($c->nombre . ' ' . $c->dni) }}">
                        <button type="button" data-dni="{{ $c->dni }}">
                            <span class="avatar" aria-hidden="true">{{ $ini }}</span>
                            <span style="min-width:0">
                                <span class="d-name d-block text-truncate">{{ $c->nombre }}</span>
                                <span class="d-dni d-block">DNI {{ $c->dni }}</span>
                            </span>
                            <span class="go">Abrir <i class="fas fa-arrow-right"></i></span>
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="empty d-none" id="noResults"><i class="fas fa-magnifying-glass"></i>Ningún huésped coincide con la búsqueda.</div>
        @endif
    </section>

    <aside class="stack sticky reveal d2">
        <div class="hero" style="padding:28px">
            @include('partials.rings', ['class' => 'hero-rings'])
            <span class="eyebrow">Cómo funciona</span>
            <h2 class="mt-3" style="color:#fbf6ea;font-size:26px">Cada noche suma, <em style="color:#e8cf9b">cada canje</em> se recuerda.</h2>
            <p style="font-size:14px">Las estadías otorgan puntos según la habitación. Los puntos se canjean por recompensas desde la libreta del huésped.</p>
        </div>
        <a href="{{ route('habitaciones.formulario') }}" class="action">
            <span class="action-icon"><i class="fas fa-key"></i></span>
            <div>
                <h3>¿Llega un huésped?</h3>
                <p class="mt-1">Asígnale una habitación para sumar sus puntos.</p>
            </div>
            <span class="action-go">Asignar habitación <i class="fas fa-arrow-right"></i></span>
        </a>
    </aside>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('consultarForm');
    const input = document.getElementById('dni');
    const items = [...document.querySelectorAll('#directory li')];
    const pill = document.getElementById('countPill');
    const none = document.getElementById('noResults');

    function abrir(dni) {
        input.value = dni;
        const btn = document.getElementById('consultarBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Abriendo…';
        form.submit();
    }

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let n = 0;
        items.forEach(li => { const ok = li.dataset.search.includes(q); li.hidden = !ok; n += ok; });
        if (pill) pill.textContent = n + (n === 1 ? ' huésped' : ' huéspedes');
        if (none) none.classList.toggle('d-none', n > 0);
    });

    // Si escriben un nombre, se abre el único resultado en vez de enviar texto como DNI
    form.addEventListener('submit', function (e) {
        const visibles = items.filter(li => !li.hidden);
        if (!/^\d+$/.test(input.value.trim()) && visibles.length === 1) {
            e.preventDefault();
            abrir(visibles[0].querySelector('button').dataset.dni);
        }
    });

    document.querySelectorAll('#directory button').forEach(b => b.addEventListener('click', () => abrir(b.dataset.dni)));
    if (input.value) input.dispatchEvent(new Event('input'));
});
</script>
@endsection
