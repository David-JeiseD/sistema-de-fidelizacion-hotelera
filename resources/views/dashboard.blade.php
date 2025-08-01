@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <!-- Header Section -->
        <div class="logo-container">
            <img src="{{ asset('images/logo.jpg') }}" alt="Hotel Buena Vida Logo" class="img-fluid">
            <h1 class="page-title mt-3">HOTEL BUENA VIDA</h1>
            <p class="text-muted">Panel de Control - Sistema de Gestión</p>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Main Actions Grid -->
        <div class="row g-4">
            <!-- Registrar Cliente -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-wrapper icon-primary mx-auto">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5 class="card-title">Registrar Cliente</h5>
                        <p class="card-text text-muted">
                            Registra nuevos clientes en el sistema para gestionar sus puntos y recompensas.
                        </p>
                        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Consultar Puntos -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-wrapper icon-warning mx-auto">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5 class="card-title">Consultar Puntos</h5>
                        <p class="card-text text-muted">
                            Consulta los puntos acumulados de los clientes y accede al flujo de canje.
                        </p>
                        <a href="{{ route('clientes.puntos') }}" class="btn btn-warning">
                            <i class="fas fa-search me-2"></i>Consultar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Asignar Habitación -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-wrapper icon-success mx-auto">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h5 class="card-title">Asignar Habitación</h5>
                        <p class="card-text text-muted">
                            Asigna habitaciones a los clientes y gestiona sus estadías de manera eficiente.
                        </p>
                        <a href="{{ route('habitaciones.asignar') }}" class="btn btn-success">
                            <i class="fas fa-key me-2"></i>Asignar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats (opcional - puedes agregar estadísticas) -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">
                    <i class="fas fa-chart-bar me-2"></i>Accesos Rápidos
                </h4>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h6>Gestión de Clientes</h6>
                    <small>Administra la base de datos de clientes</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-gift fa-2x mb-2"></i>
                    <h6>Sistema de Puntos</h6>
                    <small>Controla el programa de fidelidad</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-hotel fa-2x mb-2"></i>
                    <h6>Habitaciones</h6>
                    <small>Gestiona las asignaciones</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                    <h6>Canjes</h6>
                    <small>Administra las recompensas</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
