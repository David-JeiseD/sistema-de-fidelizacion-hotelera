<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Persona;

// Panel del super-admin (dueño de la plataforma). Solo lectura por ahora; la fase 4 agrega alta y suspensión.
class PlataformaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::withCount(['hoteles', 'clientes', 'usuarios'])->orderBy('nombre')->get();
        $personas = Persona::count();

        return view('plataforma.index', compact('empresas', 'personas'));
    }
}
