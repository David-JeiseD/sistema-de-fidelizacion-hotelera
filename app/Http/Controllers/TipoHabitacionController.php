<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Http\Request;

// Tipos de habitación del hotel activo y los puntos que otorga cada uno.
class TipoHabitacionController extends Controller
{
    public function index()
    {
        $tipos = Habitacion::where('hotel_id', Hotel::actual()->id)->withCount('visitas')
            ->orderByDesc('activo')->orderBy('puntos')->get();

        return view('tipos-habitacion.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos-habitacion.form', ['tipo' => new Habitacion()]);
    }

    public function store(Request $request)
    {
        Habitacion::create($this->validar($request) + ['hotel_id' => Hotel::actual()->id]);

        return redirect()->route('tipos-habitacion.index')->with('success', 'Tipo de habitación agregado.');
    }

    public function edit(Habitacion $tipo)
    {
        $this->delHotel($tipo);

        return view('tipos-habitacion.form', compact('tipo'));
    }

    public function update(Request $request, Habitacion $tipo)
    {
        $this->delHotel($tipo);
        $tipo->update($this->validar($request));

        return redirect()->route('tipos-habitacion.index')->with('success', 'Tipo de habitación actualizado.');
    }

    public function destroy(Habitacion $tipo)
    {
        $this->delHotel($tipo);

        // Borrar arrastraría en cascada las estadías (y los puntos) de los huéspedes.
        if ($tipo->visitas()->exists()) {
            return back()->with('error', 'Tiene estadías registradas: desactívalo en vez de eliminarlo.');
        }
        $tipo->delete();

        return redirect()->route('tipos-habitacion.index')->with('success', 'Tipo de habitación eliminado.');
    }

    private function delHotel(Habitacion $tipo): void
    {
        abort_unless((int) $tipo->hotel_id === Hotel::actual()->id, 404);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100',
            'puntos' => 'required|integer|min:0|max:100000',
        ]) + ['activo' => $request->boolean('activo')];
    }
}
