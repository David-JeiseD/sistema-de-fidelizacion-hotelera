@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">HOTEL BUENA VIDA</h2>
    <div class="d-flex justify-content-center mb-4">
        <div class="card" style="width: 18rem;">
            <img src="{{ asset('images/logo.jpg') }}" class="card-img-top" alt="Logo">
            
        </div>
    </div>
    <h2 class="mb-4">Registrar Cliente</h2>

    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingrese el nombre del cliente" required>
        </div>

        <div class="form-group">
            <label for="dni">DNI:</label>
            <input type="text" name="dni" id="dni" class="form-control" placeholder="Ingrese el DNI del cliente" maxlength="8" required>
        </div>

        <button type="submit" class="btn btn-success mt-3">Registrar</button>
    </form>
</div>
@endsection