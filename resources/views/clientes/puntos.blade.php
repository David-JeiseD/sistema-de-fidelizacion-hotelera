

@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Consultar Puntos del Cliente</h2>

    <!-- Mostrar información del cliente -->
    @if(isset($cliente))
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Cliente: {{ $cliente->nombre }}</h5>
                <p class="card-text">DNI: <strong>{{ $cliente->dni }}</strong></p>
                <p class="card-text">Total de Puntos: <strong>{{ $total_puntos }}</strong></p>
            </div>
        </div>

        <!-- Mostrar visitas del cliente -->
        <div class="mt-4">
            <h4>Visitas del Cliente</h4>
            @if($visitas->isEmpty())
                <p>No se encontraron visitas para este cliente.</p>
            @else
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Puntos Ganados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visitas as $visita)
                            <tr>
                                <td>{{ $visita->fecha }}</td>
                                <td>{{ $visita->habitacion-> nombre }}</td>
                                <td>{{ $visita->puntos }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Mostrar puntos canjeados -->
        <div class="mt-4">
            <h4>Puntos Canjeados</h4>
            @if($canjes->isEmpty())
                <p>No se encontraron canjes para este cliente.</p>
            @else
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Recompensa</th>
                            <th>Puntos Canjeados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($canjes as $canje)
                            <tr>
                                <td>{{ $canje->fecha }}</td>
                                <td>{{ $canje->recompensa->nombre }}</td>
                                <td>{{ $canje->puntos }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <!-- Formulario para redirigir al flujo de canje -->
        <form method="GET" action="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}">
            <button type="submit" class="btn btn-primary">Canjear Puntos</button>
        </form>
   
    @endif
</div>
@endsection