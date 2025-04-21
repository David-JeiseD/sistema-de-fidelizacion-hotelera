@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">HOTEL BUENA VIDA</h2>
    <div class="d-flex justify-content-center mb-4">
        <div class="card" style="width: 18rem;">
            <img src="{{ asset('images/logo.jpg') }}" class="card-img-top" alt="Logo">
            
        </div>
    </div>
    <h2 class="mb-4">Consultar Puntos del Cliente</h2>

    <form method="POST" action="{{ route('clientes.puntos.consultar') }}">
        @csrf
        <div class="form-group">
            <label for="dni">Seleccione el DNI del cliente:</label>
            <input list="dnis" name="dni" id="dni" class="form-control" placeholder="Ingrese o seleccione un DNI" required>
            <datalist id="dnis">
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->dni }}">{{ $cliente->nombre }}</option>
                @endforeach
            </datalist>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Consultar</button>
    </form>
</div>
@endsection