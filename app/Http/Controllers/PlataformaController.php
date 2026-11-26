<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Panel del super-admin: empresas clientes, su plan y su estado.
class PlataformaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::withCount(['hoteles', 'clientes', 'usuarios'])->orderBy('nombre')->get();
        $personas = Persona::count();

        return view('plataforma.index', compact('empresas', 'personas'));
    }

    public function create()
    {
        return view('plataforma.create');
    }

    // Alta completa de un cliente: empresa + primer hotel + su administrador.
    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa' => 'required|string|max:100',
            'ruc' => 'nullable|digits:11',
            'max_hoteles' => 'required|integer|min:1|max:500',
            'hotel' => 'required|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'admin_nombre' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_dni' => 'required|digits:8|unique:users,dni',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $empresa = DB::transaction(function () use ($datos) {
            $empresa = Empresa::create(['nombre' => $datos['empresa'], 'ruc' => $datos['ruc'], 'max_hoteles' => $datos['max_hoteles']]);
            $empresa->hoteles()->create(['nombre' => $datos['hotel'], 'ciudad' => $datos['ciudad']]);
            User::create([
                'empresa_id' => $empresa->id,
                'rol' => 'admin_empresa',
                'name' => $datos['admin_nombre'],
                'email' => $datos['admin_email'],
                'dni' => $datos['admin_dni'],
                'password' => $datos['admin_password'], // cast 'hashed'
            ]);

            return $empresa;
        });

        return redirect()->route('plataforma')->with('success', "{$empresa->nombre} dada de alta. Su administrador ya puede ingresar.");
    }

    public function update(Request $request, Empresa $empresa)
    {
        $datos = $request->validate([
            // No se puede bajar el plan por debajo de los hoteles que ya tiene
            'max_hoteles' => 'required|integer|max:500|min:' . max(1, $empresa->hoteles()->count()),
            'estado' => 'required|in:activa,suspendida',
        ], [
            'max_hoteles.min' => 'La empresa ya tiene :min hoteles; el plan no puede ser menor.',
        ]);

        $empresa->update($datos);

        return back()->with('success', "{$empresa->nombre}: plan de {$empresa->max_hoteles} " . str('hotel')->plural($empresa->max_hoteles) . ', cuenta ' . $empresa->estado . '.');
    }
}
