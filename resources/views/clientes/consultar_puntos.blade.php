@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <!-- Header Section -->
        <div class="logo-container">
            <img src="{{ asset('images/logo.jpg') }}" alt="Hotel Buena Vida Logo" class="img-fluid">
            <h1 class="page-title mt-3">HOTEL BUENA VIDA</h1>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-search me-2"></i>Consultar Puntos del Cliente
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clientes.puntos.consultar') }}" id="consultarForm">
                            @csrf
                            <div class="mb-4">
                                <label for="dni" class="form-label">
                                    <i class="fas fa-id-card me-2"></i>Seleccione el DNI del cliente
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input list="dnis" 
                                           name="dni" 
                                           id="dni" 
                                           class="form-control" 
                                           placeholder="Ingrese o seleccione un DNI" 
                                           required
                                           autocomplete="off">
                                    <datalist id="dnis">
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->dni }}">{{ $cliente->nombre }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Puede escribir el DNI o seleccionar de la lista
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary" id="consultarBtn">
                                    <i class="fas fa-search me-2"></i>Consultar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                <h6>¿Cliente nuevo?</h6>
                                <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm">
                                    Registrar Cliente
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-bed fa-2x text-info mb-2"></i>
                                <h6>Asignar habitación</h6>
                                <a href="{{ route('habitaciones.asignar') }}" class="btn btn-info btn-sm">
                                    Ir a Asignación
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('consultarForm').addEventListener('submit', function() {
    const btn = document.getElementById('consultarBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Consultando...';
    btn.disabled = true;
});

// Auto-complete enhancement
document.getElementById('dni').addEventListener('input', function() {
    const value = this.value;
    if (value.length >= 3) {
        // Add visual feedback for valid input
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else {
        this.classList.remove('is-valid');
    }
});
</script>
@endsection
