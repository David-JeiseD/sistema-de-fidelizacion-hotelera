@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0">
                <i class="fas fa-star me-2"></i>Consulta de Puntos
            </h2>
            <a href="{{ route('clientes.puntos') }}" class="btn btn-outline-primary">
                <i class="fas fa-search me-2"></i>Nueva Consulta
            </a>
        </div>

        @if(isset($cliente))
            <!-- Cliente Info Card -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Información del Cliente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong><i class="fas fa-user me-2"></i>Nombre:</strong> 
                                        {{ $cliente->nombre }}
                                    </p>
                                    <p class="mb-0">
                                        <strong><i class="fas fa-id-card me-2"></i>DNI:</strong> 
                                        {{ $cliente->dni }}
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="stats-card d-inline-block">
                                        <i class="fas fa-star fa-2x mb-2"></i>
                                        <h3 class="mb-0">{{ $total_puntos }}</h3>
                                        <small>Puntos Totales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <i class="fas fa-gift fa-3x text-success mb-3"></i>
                            <form method="GET" action="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-exchange-alt me-2"></i>Canjear Puntos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitas Section -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Historial de Visitas
                    </h5>
                </div>
                <div class="card-body">
                    @if($visitas->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron visitas para este cliente.</p>
                            <a href="{{ route('habitaciones.asignar') }}" class="btn btn-info">
                                <i class="fas fa-plus me-2"></i>Asignar Primera Habitación
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-calendar me-2"></i>Fecha</th>
                                        <th><i class="fas fa-bed me-2"></i>Habitación</th>
                                        <th><i class="fas fa-star me-2"></i>Puntos Ganados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visitas as $visita)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ \Carbon\Carbon::parse($visita->fecha)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-door-open me-2 text-info"></i>
                                                {{ $visita->habitacion->nombre }}
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-star me-1"></i>{{ $visita->puntos }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Canjes Section -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>Historial de Canjes
                    </h5>
                </div>
                <div class="card-body">
                    @if($canjes->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron canjes para este cliente.</p>
                            @if($total_puntos > 0)
                                <form method="GET" action="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}" class="d-inline">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-gift me-2"></i>Realizar Primer Canje
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-calendar me-2"></i>Fecha</th>
                                        <th><i class="fas fa-gift me-2"></i>Recompensa</th>
                                        <th><i class="fas fa-star me-2"></i>Puntos Canjeados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($canjes as $canje)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ \Carbon\Carbon::parse($canje->fecha)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-award me-2 text-warning"></i>
                                                {{ $canje->recompensa->nombre }}
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-minus me-1"></i>{{ $canje->puntos }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Animate numbers
    const pointsElement = document.querySelector('.stats-card h3');
    if (pointsElement) {
        const finalValue = parseInt(pointsElement.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 20);
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            pointsElement.textContent = currentValue;
        }, 50);
    }
});
</script>
@endsection
