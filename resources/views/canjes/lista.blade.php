@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Seleccionar Recompensa</h2>

    <!-- Mostrar mensajes de éxito o error -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm ml-3">Ir al Dashboard</a>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Mostrar lista de recompensas -->
    <div class="row">
        @foreach($recompensas as $recompensa)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $recompensa->nombre }}</h5>
                        <p class="card-text">Puntos requeridos: <strong>{{ $recompensa->puntos }}</strong></p>
                        <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id, 'opcion' => $recompensa->id]) }}" class="btn btn-primary">Seleccionar</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Mostrar detalles de la recompensa seleccionada -->
    @if($recompensaSeleccionada)
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Recompensa Seleccionada: {{ $recompensaSeleccionada->nombre }}</h5>
                <p class="card-text">Puntos requeridos: <strong>{{ $recompensaSeleccionada->puntos }}</strong></p>
                <p class="card-text">Cliente: <strong>{{ $cliente->nombre }}</strong></p>
                <p class="card-text">Puntos disponibles: <strong>{{ $cliente->total_puntos }}</strong></p>

                <!-- Formulario para confirmar el canje -->
                <form method="POST" action="{{ route('canjes.realizar') }}">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="recompensa_id" value="{{ $recompensaSeleccionada->id }}">
                    <button type="submit" class="btn btn-success">Confirmar Canje</button>
                    <a href="{{ route('canjes.lista', ['cliente_id' => $cliente->id]) }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection