<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            // Sin SVG: puede llevar scripts.
            'logo' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'quitar_logo' => 'nullable|boolean',
        ], [
            'ruc.digits' => 'El RUC debe tener 11 dígitos.',
            'telefono.regex' => 'El teléfono solo puede tener números, espacios, +, guiones y paréntesis.',
            'sitio_web.url' => 'Escribe la dirección completa, por ejemplo https://buenavida.pe',
            'logo.mimes' => 'El logo debe ser una imagen JPG, PNG o WEBP.',
            'logo.max' => 'El logo no puede pesar más de 2 MB.',
        ]);

        $hotel = Establecimiento::actual();
        unset($datos['logo'], $datos['quitar_logo']);

        if ($request->hasFile('logo') || $request->boolean('quitar_logo')) {
            if ($hotel->logo_path) {
                Storage::disk('public')->delete($hotel->logo_path);
            }
            $datos['logo_path'] = $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : null;
        }

        $hotel->update($datos);

        return redirect()->route('establecimiento.edit')->with('success', 'Datos del establecimiento guardados.');
    }
}
