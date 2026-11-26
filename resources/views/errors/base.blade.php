<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · Fidelización hotelera</title>
    <link href="https://fonts.bunny.net/css?family=fraunces:400,500|manrope:400,600,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/hotel.css') }}" rel="stylesheet">
</head>
<body>
    {{-- Independiente del layout: una página de error no debe depender del hotel activo ni de la sesión. --}}
    <main style="min-height:100vh;display:grid;place-items:center;padding:24px">
        <section class="panel panel-pad reveal text-center" style="max-width:460px;text-align:center">
            <div class="action-icon" style="margin:0 auto 18px;width:58px;height:58px;font-size:22px"><i class="fas {{ $icono }}"></i></div>
            <span class="eyebrow">Error {{ $codigo }}</span>
            <h1 class="page-title" style="font-size:32px">{{ $titulo }}</h1>
            <p class="muted mt-2" style="margin:10px 0 24px">{{ $mensaje }}</p>
            <a href="{{ url('/') }}" class="btn-hv"><i class="fas fa-arrow-left"></i>Volver al inicio</a>
        </section>
    </main>
</body>
</html>
