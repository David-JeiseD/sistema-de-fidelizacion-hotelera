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
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Cliente
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clientes.store') }}" id="registrarForm" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="nombre" class="form-label">
                                            <i class="fas fa-user me-2"></i>Nombre Completo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="nombre" 
                                               id="nombre" 
                                               class="form-control @error('nombre') is-invalid @enderror" 
                                               placeholder="Ingrese el nombre completo del cliente" 
                                               value="{{ old('nombre') }}"
                                               required>
                                        @error('nombre')
                                            <div class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                        <div class="valid-feedback">
                                            ¡Perfecto!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="dni" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>DNI <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="dni" 
                                               id="dni" 
                                               class="form-control @error('dni') is-invalid @enderror" 
                                               placeholder="Ingrese el DNI (8 dígitos)" 
                                               maxlength="8" 
                                               pattern="[0-9]{8}"
                                               value="{{ old('dni') }}"
                                               required>
                                        @error('dni')
                                            <div class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                        <div class="valid-feedback">
                                            DNI válido
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Debe contener exactamente 8 dígitos
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Card -->
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Información:</strong> Una vez registrado, el cliente podrá acumular puntos por cada estadía y canjearlos por recompensas.
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success" id="registrarBtn">
                                    <i class="fas fa-save me-2"></i>Registrar Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                <h6>Consultar Puntos</h6>
                                <a href="{{ route('clientes.puntos') }}" class="btn btn-primary btn-sm">
                                    Consultar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-bed fa-2x text-info mb-2"></i>
                                <h6>Asignar Habitación</h6>
                                <a href="{{ route('habitaciones.asignar') }}" class="btn btn-info btn-sm">
                                    Asignar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-gift fa-2x text-warning mb-2"></i>
                                <h6>Ver Recompensas</h6>
                                <button class="btn btn-warning btn-sm" onclick="showToast('Primero debe consultar un cliente', 'info')">
                                    Ver Canjes
                                </button>
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
// Form validation and enhancement
document.getElementById('registrarForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('registrarBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registrando...';
    btn.disabled = true;
});

// DNI validation
document.getElementById('dni').addEventListener('input', function() {
    const value = this.value;
    const isValid = /^[0-9]{8}$/.test(value);
    
    if (value.length === 8 && isValid) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else if (value.length > 0) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});

// Name validation
document.getElementById('nombre').addEventListener('input', function() {
    const value = this.value.trim();
    
    if (value.length >= 3) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else if (value.length > 0) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});

// Only numbers for DNI
document.getElementById('dni').addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
        e.preventDefault();
    }
});
</script>
@endsection
