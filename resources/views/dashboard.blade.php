@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">HOTEL BUENA VIDA</h2>
    <div class="d-flex justify-content-center mb-4">
        <div class="card" style="width: 18rem;">
            <img src="{{ asset('images/logo.jpg') }}" class="card-img-top" alt="Logo">
            
        </div>
    </div>
    <h2 class="mb-4 text-center">Panel de Control</h2>

    <!-- Mostrar mensajes de éxito o error -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Contenido del dashboard -->
    <div class="row">
        <!-- Card: Registrar Cliente -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="row no-gutters align-items-center">
                    <div class="col-4">
                        <img src="{{ asset('images/registrar.png') }}" class="card-img img-fluid" alt="Registrar Cliente">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <h5 class="card-title">Registrar Cliente</h5>
                            <p class="card-text">Registra nuevos clientes en el sistema para gestionar sus puntos y recompensas.</p>
                            <a href="{{ route('clientes.create') }}" class="btn btn-primary">Registrar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Consultar Puntos -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="row no-gutters align-items-center">
                    <div class="col-4">
                        <img src="{{ asset('images/puntos.png') }}" class="card-img img-fluid" alt="Consultar Puntos">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <h5 class="card-title">Consultar Puntos</h5>
                            <p class="card-text">Consulta los puntos acumulados de los clientes y accede al flujo de canje.</p>
                            <a href="{{ route('clientes.puntos') }}" class="btn btn-primary">Consultar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Asignar Habitación -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="row no-gutters align-items-center">
                    <div class="col-4">
                        <img src="{{ asset('images/asignar.png') }}" class="card-img img-fluid" alt="Asignar Habitación">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <h5 class="card-title">Asignar Habitación</h5>
                            <p class="card-text">Asigna habitaciones a los clientes y gestiona sus estadías de manera eficiente.</p>
                            <a href="{{ route('habitaciones.asignar') }}" class="btn btn-primary">Asignar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection