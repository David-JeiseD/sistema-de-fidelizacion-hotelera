<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

// Cambia el hotel en el que trabaja el usuario (solo entre los de su empresa).
class HotelActivoController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['hotel_id' => 'required|integer']);

        $hotel = Hotel::findOrFail($request->hotel_id); // el scope de empresa da 404 si es ajeno
        $request->session()->put(Hotel::SESION, $hotel->id);

        return back()->with('success', 'Ahora trabajas en ' . $hotel->nombre . '.');
    }
}
