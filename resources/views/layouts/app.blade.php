<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f3d36">
    <title>@hasSection('title')@yield('title') · @endif {{ auth()->user()?->esSuperAdmin() ? 'Fidelización hotelera' : $hotel->nombre }}</title>
    <link rel="icon" href="{{ $hotel->logo_url }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:400,400i,500,500i,600|manrope:400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/hotel.css') }}?v={{ filemtime(public_path('css/hotel.css')) }}" rel="stylesheet">
</head>
<body>
@auth
    @php
        $usuario = Auth::user();
        $operan = ['admin_empresa', 'gerente', 'recepcionista'];
        $gestionan = ['admin_empresa', 'gerente'];
        // [ruta, patrón activo, ícono, texto, roles que la ven] — mismos roles que en routes/web.php
        $nav = [
            'Operaciones' => [
                ['dashboard', 'dashboard', 'fa-compass', 'Panel', $operan],
                ['clientes.create', 'clientes.create', 'fa-user-plus', 'Registrar huésped', $operan],
                ['habitaciones.formulario', 'habitaciones.*', 'fa-key', 'Asignar habitación', $operan],
                ['clientes.puntos', ['clientes.puntos*', 'clientes.libreta', 'canjes.*'], 'fa-book-open', 'Libreta de puntos', $operan],
                ['cobrar', 'cobrar', 'fa-qrcode', 'Cobrar con QR', $operan],
            ],
            'Este hotel' => [
                ['establecimiento.edit', 'establecimiento.*', 'fa-building', 'Establecimiento', $gestionan],
                ['tipos-habitacion.index', 'tipos-habitacion.*', 'fa-bed', 'Tipos de habitación', $gestionan],
                ['metodos-pago.index', 'metodos-pago.*', 'fa-wallet', 'Métodos de pago', $gestionan],
                ['personal.index', ['personal.*', 'register'], 'fa-id-badge', 'Personal', $gestionan],
            ],
            'Empresa' => [
                ['hoteles.index', 'hoteles.*', 'fa-hotel', 'Hoteles', ['admin_empresa']],
                ['recompensas.index', 'recompensas.*', 'fa-gift', 'Recompensas', ['admin_empresa']],
            ],
            'Plataforma' => [
                ['plataforma', 'plataforma*', 'fa-globe', 'Empresas', ['super_admin']],
            ],
        ];
        $nav = array_filter(array_map(fn ($items) => array_filter($items, fn ($i) => $usuario->tieneRol(...$i[4])), $nav));
        $iniciales = collect(explode(' ', trim(Auth::user()->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
    @endphp
    <div class="shell">
        <aside class="sidebar" aria-label="Navegación principal">
            <a class="brand" href="{{ route($usuario->esSuperAdmin() ? 'plataforma' : 'dashboard') }}">
                <span class="brand-seal"><img src="{{ $usuario->esSuperAdmin() ? asset('images/logo.jpg') : $hotel->logo_url }}" alt=""></span>
                <span>
                    <span class="brand-name d-block">{{ $usuario->esSuperAdmin() ? 'Plataforma' : $hotel->nombre_corto }}</span>
                    <span class="brand-sub d-block">{{ $usuario->esSuperAdmin() ? 'Fidelización hotelera' : $hotel->eslogan }}</span>
                </span>
            </a>

            @unless ($usuario->esSuperAdmin())
            @php $hotelesEmpresa = \App\Models\Hotel::permitidos()->orderBy('nombre')->get(['id', 'nombre']); @endphp
            <div class="hotel-switch">
                <span class="nav-label p-0">Trabajando en</span>
                @if ($hotelesEmpresa->count() > 1)
                    <form method="POST" action="{{ route('hotel-activo') }}">
                        @csrf
                        <label class="visually-hidden" for="hotelActivo">Hotel activo</label>
                        <select id="hotelActivo" name="hotel_id" onchange="this.form.submit()">
                            @foreach ($hotelesEmpresa as $h)
                                <option value="{{ $h->id }}" @selected($h->id === $hotel->id)>{{ $h->nombre }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit">Cambiar</button></noscript>
                    </form>
                @else
                    <b title="{{ $hotel->nombre }}">{{ $hotel->nombre }}</b>
                @endif
            </div>
            @endunless

            @foreach ($nav as $grupo => $items)
                <nav class="nav-group">
                    <div class="nav-label">{{ $grupo }}</div>
                    @foreach ($items as [$ruta, $patron, $icono, $texto])
                        @php $activo = request()->routeIs(...(array) $patron); @endphp
                        <a href="{{ route($ruta) }}" class="nav-link-hv {{ $activo ? 'active' : '' }}" @if($activo) aria-current="page" @endif title="{{ $texto }}">
                            <i class="fas {{ $icono }}"></i><span>{{ $texto }}</span>
                        </a>
                    @endforeach
                </nav>
            @endforeach

            <div class="sidebar-foot">
                <span class="avatar" aria-hidden="true">{{ $iniciales }}</span>
                <div class="user-meta" style="min-width:0">
                    <div class="user-name text-truncate">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ $usuario->rol_nombre }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="logout-btn" title="Cerrar sesión" aria-label="Cerrar sesión">
                        <i class="fas fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main">
            <div class="main-inner">
                @yield('content')
            </div>
        </main>
    </div>
@else
    <main class="guest-main">
        @yield('content')
    </main>
@endauth

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast hv-toast" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
            <div class="toast-body">
                <span class="t-icon"><i id="toast-icon" class="fas fa-info"></i></span>
                <div>
                    <div id="toast-title" class="t-title">Notificación</div>
                    <div id="toast-body" class="t-msg"></div>
                </div>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="t-bar"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showToast(message, type = 'info', title = 'Notificación') {
        const toast = document.getElementById('liveToast');
        const icons = { success: 'fa-check', error: 'fa-xmark', warning: 'fa-exclamation', info: 'fa-info' };
        toast.dataset.type = type;
        document.getElementById('toast-icon').className = 'fas ' + (icons[type] || icons.info);
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-body').textContent = message;
        // Reinicia la barra de tiempo
        const bar = toast.querySelector('.t-bar');
        bar.style.animation = 'none'; bar.offsetHeight; bar.style.animation = '';
        bootstrap.Toast.getOrCreateInstance(toast).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success')) showToast(@json(session('success')), 'success', 'Listo'); @endif
        @if (session('error')) showToast(@json(session('error')), 'error', 'No se pudo completar'); @endif
        @if (session('warning')) showToast(@json(session('warning')), 'warning', 'Atención'); @endif
        {{-- Los formularios muestran sus errores junto al campo; estos vienen de campos ocultos --}}
        @if ($errors->hasAny(['cliente_id', 'recompensa_id'])) showToast(@json($errors->first()), 'error', 'No se pudo completar'); @endif
    });
    </script>
    @yield('scripts')
    @stack('card-script')
</body>
</html>
