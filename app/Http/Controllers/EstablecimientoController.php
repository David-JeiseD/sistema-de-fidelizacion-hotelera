<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;

class EstablecimientoController extends Controller
{
    public function edit()
    {
        return view('establecimiento.edit', ['hotel' => Establecimiento::actual()]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'eslogan' => 'nullable|string|max:150',
            'ruc' => 'nullable|digits:11',
            'direccion' => 'nullable|string|max:200',
            'ciudad' => 'nullable|string|max:100',
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'email' => 'nullable|email|max:150',
            'sitio_web' => 'nullable|url|max:150',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ], [
            'ruc.digits' => 'El RUC debe tener 11 dígitos.',
            'telefono.regex' => 'El teléfono solo puede tener números, espacios, +, guiones y paréntesis.',
            'sitio_web.url' => 'Escribe la dirección completa, por ejemplo https://buenavida.pe',
        ]);

        Establecimiento::actual()->update($datos);

        return redirect()->route('establecimiento.edit')->with('success', 'Datos del establecimiento guardados.');
    }
}
