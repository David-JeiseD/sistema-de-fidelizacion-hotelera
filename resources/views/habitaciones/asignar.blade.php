@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">HOTEL BUENA VIDA</h2>
    <div class="d-flex justify-content-center mb-4">
        <div class="card" style="width: 18rem;">
            <img src="{{ asset('images/logo.jpg') }}" class="card-img-top" alt="Logo">
            
        </div>
    </div>
    <h2 class="mb-4 text-center">Asignar Habitación</h2>

    <!-- Formulario para asignar habitación -->
    <form method="POST" action="{{ route('habitaciones.asignar') }}" class="card p-4 shadow-sm">
        @csrf

        <!-- DNI del cliente -->
        <div class="form-group">
            <label for="dni">DNI del Cliente:</label>
            <input type="text" name="dni" id="dni" class="form-control" placeholder="Ingrese el DNI del cliente" maxlength="8" required>
        </div>

        <!-- Seleccionar habitación -->
        <div class="form-group">
            <label for="habitacion_id">Seleccionar Habitación:</label>
            <select name="habitacion_id" id="habitacion_id" class="form-control" required>
                <option value="" disabled selected>Seleccione una habitación</option>
                @foreach($habitaciones as $habitacion)
                    <option value="{{ $habitacion->id }}">{{ $habitacion->nombre }} ({{ $habitacion->puntos }} pts)</option>
                @endforeach
            </select>
        </div>

        <!-- Botón de asignar -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary mt-3">Asignar Habitación</button>
        </div>
    </form>
</div>
@endsection