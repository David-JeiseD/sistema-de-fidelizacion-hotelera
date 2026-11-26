<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MetodoPagoController extends Controller
{
    public function index()
    {
        $metodos = MetodoPago::where('hotel_id', Hotel::actual()->id)->orderByDesc('activo')->orderBy('nombre')->get();
        return view('metodos-pago.index', compact('metodos'));
    }

    public function create()
    {
        return view('metodos-pago.form', ['metodo' => new MetodoPago(['activo' => true])]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request, qrObligatorio: true);
        $datos['qr_path'] = $request->file('qr')->store('qr', 'public');
        $datos['hotel_id'] = Hotel::actual()->id;

        MetodoPago::create($datos);

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago agregado.');
    }

    public function edit(MetodoPago $metodo)
    {
        $this->delHotel($metodo);
        return view('metodos-pago.form', compact('metodo'));
    }

    public function update(Request $request, MetodoPago $metodo)
    {
        $this->delHotel($metodo);
        $datos = $this->validar($request, qrObligatorio: false);

        if ($request->hasFile('qr')) {
            Storage::disk('public')->delete($metodo->qr_path);
            $datos['qr_path'] = $request->file('qr')->store('qr', 'public');
        }

        $metodo->update($datos);

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago actualizado.');
    }

    public function destroy(MetodoPago $metodo)
    {
        $this->delHotel($metodo);
        Storage::disk('public')->delete($metodo->qr_path);
        $metodo->delete();

        return redirect()->route('metodos-pago.index')->with('success', 'Método de pago eliminado.');
    }

    // Pantalla para mostrar el QR al huésped en recepción.
    public function cobrar()
    {
        return view('metodos-pago.cobrar', [
            'metodos' => MetodoPago::activos()->where('hotel_id', Hotel::actual()->id)->orderBy('nombre')->get(),
            'hotel' => Hotel::actual(),
        ]);
    }

    // Un hotel no puede ver ni tocar los métodos de otro (404 en vez de 403 para no revelar que existe).
    private function delHotel(MetodoPago $metodo): void
    {
        abort_unless((int) $metodo->hotel_id === Hotel::actual()->id, 404);
    }

    private function validar(Request $request, bool $qrObligatorio): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:50',
            'titular' => 'nullable|string|max:100',
            'numero' => ['required', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            // Sin SVG: puede llevar scripts.
            'qr' => [$qrObligatorio ? 'required' : 'nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'numero.regex' => 'El número solo puede tener dígitos, espacios, +, guiones y paréntesis.',
            'qr.required' => 'Sube la imagen del código QR.',
            'qr.mimes' => 'El QR debe ser una imagen JPG, PNG o WEBP.',
            'qr.max' => 'El QR no puede pesar más de 2 MB.',
        ]);

        unset($datos['qr']);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
