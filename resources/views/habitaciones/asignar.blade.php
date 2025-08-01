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
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-bed me-2"></i>Asignar Habitación
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('habitaciones.asignar') }}" id="asignarForm" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="dni" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>DNI del Cliente <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="dni" 
                                               id="dni" 
                                               class="form-control @error('dni') is-invalid @enderror" 
                                               placeholder="Ingrese el DNI del cliente" 
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
                                            Ingrese el DNI de 8 dígitos del cliente
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="habitacion_id" class="form-label">
                                            <i class="fas fa-door-open me-2"></i>Seleccionar Habitación <span class="text-danger">*</span>
                                        </label>
                                        <select name="habitacion_id" 
                                                id="habitacion_id" 
                                                class="form-control @error('habitacion_id') is-invalid @enderror" 
                                                required>
                                            <option value="" disabled selected>Seleccione una habitación</option>
                                            @foreach($habitaciones as $habitacion)
                                                <option value="{{ $habitacion->id }}" 
                                                        data-puntos="{{ $habitacion->puntos }}"
                                                        {{ old('habitacion_id') == $habitacion->id ? 'selected' : '' }}>
                                                    {{ $habitacion->nombre }} ({{ $habitacion->puntos }} puntos)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('habitacion_id')
                                            <div class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-star me-1"></i>
                                            Los puntos se asignarán automáticamente
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Card -->
                            <div id="previewCard" class="card border-success mb-4" style="display: none;">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-eye me-2"></i>Vista Previa de la Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>DNI Cliente:</strong> <span id="previewDni">-</span></p>
                                            <p><strong>Habitación:</strong> <span id="previewHabitacion">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Puntos a otorgar:</strong> 
                                                <span id="previewPuntos" class="badge bg-warning">0</span>
                                            </p>
                                            <p><strong>Fecha:</strong> {{ date('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Alert -->
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Información:</strong> Al asignar una habitación, el cliente recibirá automáticamente los puntos correspondientes a esa habitación.
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-info" id="asignarBtn">
                                    <i class="fas fa-key me-2"></i>Asignar Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Available Rooms Grid -->
                <div class="mt-4">
                    <h5 class="mb-3">
                        <i class="fas fa-list me-2"></i>Habitaciones Disponibles
                    </h5>
                    <div class="row">
                        @foreach($habitaciones as $habitacion)
                            <div class="col-md-4 mb-3">
                                <div class="card border-info room-card" data-room-id="{{ $habitacion->id }}">
                                    <div class="card-body text-center">
                                        <i class="fas fa-bed fa-2x text-info mb-2"></i>
                                        <h6 class="card-title">{{ $habitacion->nombre }}</h6>
                                        <p class="card-text">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star me-1"></i>{{ $habitacion->puntos }} puntos
                                            </span>
                                        </p>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm select-room" 
                                                data-room-id="{{ $habitacion->id }}"
                                                data-room-name="{{ $habitacion->nombre }}"
                                                data-room-points="{{ $habitacion->puntos }}">
                                            Seleccionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dniInput = document.getElementById('dni');
    const habitacionSelect = document.getElementById('habitacion_id');
    const previewCard = document.getElementById('previewCard');
    const asignarForm = document.getElementById('asignarForm');
    const asignarBtn = document.getElementById('asignarBtn');

    // DNI validation
    dniInput.addEventListener('input', function() {
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
        updatePreview();
    });

    // Room selection
    habitacionSelect.addEventListener('change', function() {
        updatePreview();
    });

    // Room card selection
    document.querySelectorAll('.select-room').forEach(button => {
        button.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            habitacionSelect.value = roomId;
            
            // Visual feedback
            document.querySelectorAll('.room-card').forEach(card => {
                card.classList.remove('border-success');
                card.classList.add('border-info');
            });
            
            this.closest('.room-card').classList.remove('border-info');
            this.closest('.room-card').classList.add('border-success');
            
            updatePreview();
            showToast('Habitación seleccionada: ' + this.dataset.roomName, 'success');
        });
    });

    // Update preview
    function updatePreview() {
        const dni = dniInput.value;
        const selectedOption = habitacionSelect.options[habitacionSelect.selectedIndex];
        
        if (dni && selectedOption.value) {
            document.getElementById('previewDni').textContent = dni;
            document.getElementById('previewHabitacion').textContent = selectedOption.text;
            document.getElementById('previewPuntos').textContent = selectedOption.dataset.puntos || '0';
            previewCard.style.display = 'block';
        } else {
            previewCard.style.display = 'none';
        }
    }

    // Form submission
    asignarForm.addEventListener('submit', function() {
        asignarBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Asignando...';
        asignarBtn.disabled = true;
    });

    // Only numbers for DNI
    dniInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
