<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Http\Request;

// Hoteles de la empresa (solo el administrador). El límite lo pone el plan contratado.
class HotelController extends Controller
{
    public function index()
    {
        $hoteles = Hotel::withCount(['visitas', 'usuarios'])->orderBy('nombre')->get();
        $empresa = Hotel::actual()->empresa;

        return view('hoteles.index', compact('hoteles', 'empresa'));
    }

    public function create()
    {
        $empresa = Hotel::actual()->empresa;
        if ($this->enElLimite()) {
            return redirect()->route('hoteles.index')->with('error', "Tu plan permite {$empresa->max_hoteles} " . str('hotel')->plural($empresa->max_hoteles) . '. Contacta a tu proveedor para ampliarlo.');
        }

        return view('hoteles.create');
    }

    public function store(Request $request)
    {
        abort_if($this->enElLimite(), 403, 'Tu plan no permite más hoteles.');

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:200',
            'zona_horaria' => 'required|timezone:all',
        ]);

        $origen = Hotel::actual();
        $hotel = Hotel::create($datos); // empresa_id lo pone el scope

        if ($request->boolean('copiar_habitaciones')) {
            Habitacion::where('hotel_id', $origen->id)->where('activo', true)->get()
                ->each(fn ($h) => Habitacion::create(['hotel_id' => $hotel->id, 'nombre' => $h->nombre, 'puntos' => $h->puntos]));
        }

        $request->session()->put(Hotel::SESION, $hotel->id);

        return redirect()->route('establecimiento.edit')->with('success', "{$hotel->nombre} creado. Ahora trabajas en él: completa sus datos y su QR de pago.");
    }

    // Entrar a administrar otro hotel = cambiar el hotel activo e ir a sus datos.
    public function administrar(Request $request, Hotel $hotel)
    {
        $request->session()->put(Hotel::SESION, $hotel->id); // el binding ya da 404 si es ajeno

        return redirect()->route('establecimiento.edit');
    }

    private function enElLimite(): bool
    {
        return Hotel::count() >= Hotel::actual()->empresa->max_hoteles;
    }
}
