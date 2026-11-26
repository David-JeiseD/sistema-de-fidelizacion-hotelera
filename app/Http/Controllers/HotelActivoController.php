<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

// Cambia el hotel en el que trabaja el usuario (solo entre los que tiene permitidos).
class HotelActivoController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['hotel_id' => 'required|integer']);

        $hotel = Hotel::permitidos()->findOrFail($request->hotel_id); // 404 si es de otra empresa o no está asignado
        $request->session()->put(Hotel::SESION, $hotel->id);

        return back()->with('success', 'Ahora trabajas en ' . $hotel->nombre . '.');
    }
}
