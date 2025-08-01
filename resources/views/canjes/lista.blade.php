@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
                <i class="fas fa-gift me-2"></i>Sistema de Recompensas
            </h2>
            <a href="{{ route('clientes.puntos') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Consulta
            </a>
        </div>

        <!-- Cliente Info -->
        @if(isset($cliente))
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>{{ $cliente->nombre }}
                            </h5>
                            <small>DNI: {{ $cliente->dni }}</small>
                        </div>
                        <div class="col-auto">
                            <div class="stats-card">
                                <i class="fas fa-star fa-2x mb-1"></i>
                                <h4 class="mb-0">{{ $cliente->total_puntos }}</h4>
                                <small>Puntos Disponibles</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recompensas Grid -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">
                    <i class="fas fa-award me-2"></i>Recompensas Disponibles
                </h4>
            </div>
            
            @foreach($recompensas as $recompensa)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 reward-card {{ isset($cliente) && $cliente->total_puntos >= $recompensa->puntos ? 'border-success' : 'border-warning' }}">
                        <div class="card-body text-center">
                            <div class="icon-wrapper {{ isset($cliente) && $cliente->total_puntos >= $recompensa->puntos ? 'icon-success' : 'icon-warning' }} mx-auto">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h5 class="card-title">{{ $recompensa->nombre }}</h5>
                            <p class="card-text">
                                <span class="badge {{ isset($cliente) && $cliente->total_puntos >= $recompensa->puntos ? 'bg-success' : 'bg-warning' }}">
                                    <i class="fas fa-star me-1"></i>{{ $recompensa->puntos }} puntos
                                </span>
                            </p>
                            
                            @if(isset($cliente))
                                @if($cliente->total_puntos >= $recompensa->puntos)
                                    <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id, 'opcion' => $recompensa->id]) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Seleccionar
                                    </a>
                                @else
                                    <button class="btn btn-outline-warning" disabled>
                                        <i class="fas fa-lock me-2"></i>Puntos Insuficientes
                                    </button>
                                    <small class="d-block mt-2 text-muted">
                                        Necesitas {{ $recompensa->puntos - $cliente->total_puntos }} puntos más
                                    </small>
                                @endif
                            @else
                                <button class="btn btn-outline-secondary" disabled>
                                    Selecciona un cliente primero
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recompensa Seleccionada -->
        @if($recompensaSeleccionada)
            <div class="card border-success mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Confirmar Canje
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Detalles del Canje:</h6>
                            <ul class="list-unstyled">
                                <li><strong><i class="fas fa-gift me-2"></i>Recompensa:</strong> {{ $recompensaSeleccionada->nombre }}</li>
                                <li><strong><i class="fas fa-star me-2"></i>Puntos requeridos:</strong> {{ $recompensaSeleccionada->puntos }}</li>
                                <li><strong><i class="fas fa-user me-2"></i>Cliente:</strong> {{ $cliente->nombre }}</li>
                                <li><strong><i class="fas fa-wallet me-2"></i>Puntos disponibles:</strong> {{ $cliente->total_puntos }}</li>
                                <li><strong><i class="fas fa-calculator me-2"></i>Puntos restantes:</strong> 
                                    <span class="badge bg-info">{{ $cliente->total_puntos - $recompensaSeleccionada->puntos }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-3x mb-2"></i>
                                <h6>¡Canje Disponible!</h6>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <form method="POST" action="{{ route('canjes.realizar') }}" class="d-inline" id="canjeForm">
                            @csrf
                            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                            <input type="hidden" name="recompensa_id" value="{{ $recompensaSeleccionada->id }}">
                            <button type="submit" class="btn btn-success" id="confirmarBtn">
                                <i class="fas fa-exchange-alt me-2"></i>Confirmar Canje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($recompensas->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-gift fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay recompensas disponibles</h4>
                <p class="text-muted">Contacta al administrador para configurar las recompensas.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    const canjeForm = document.getElementById('canjeForm');
    if (canjeForm) {
        canjeForm.addEventListener('submit', function(e) {
            const confirmarBtn = document.getElementById('confirmarBtn');
            confirmarBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            confirmarBtn.disabled = true;
            
            // Show confirmation toast
            showToast('Procesando canje...', 'info', 'Procesando');
        });
    }

    // Add hover effects to reward cards
    document.querySelectorAll('.reward-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endsection
