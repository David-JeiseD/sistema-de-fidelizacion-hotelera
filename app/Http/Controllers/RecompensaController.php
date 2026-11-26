<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Recompensa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Catálogo de recompensas de la empresa (opcionalmente exclusivas de un hotel). Solo el admin.
class RecompensaController extends Controller
{
    public function index()
    {
        $recompensas = Recompensa::with('hotel')->withCount('canjes')->orderByDesc('activo')->orderBy('puntos')->get();

        return view('recompensas.index', compact('recompensas'));
    }

    public function create()
    {
        return view('recompensas.form', ['recompensa' => new Recompensa(), 'hoteles' => Hotel::orderBy('nombre')->get(['id', 'nombre'])]);
    }

    public function store(Request $request)
    {
        Recompensa::create($this->validar($request));

        return redirect()->route('recompensas.index')->with('success', 'Recompensa agregada al catálogo.');
    }

    public function edit(Recompensa $recompensa)
    {
        return view('recompensas.form', ['recompensa' => $recompensa, 'hoteles' => Hotel::orderBy('nombre')->get(['id', 'nombre'])]);
    }

    public function update(Request $request, Recompensa $recompensa)
    {
        $recompensa->update($this->validar($request));

        return redirect()->route('recompensas.index')->with('success', 'Recompensa actualizada.');
    }

    public function destroy(Recompensa $recompensa)
    {
        // Borrar arrastraría en cascada los canjes del historial de los huéspedes.
        if ($recompensa->canjes()->exists()) {
            return back()->with('error', 'Ya fue canjeada: desactívala en vez de eliminarla.');
        }
        $recompensa->delete();

        return redirect()->route('recompensas.index')->with('success', 'Recompensa eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100',
            'puntos' => 'required|integer|min:1|max:1000000',
            // exists no pasa por el scope: se acota a los hoteles de la empresa a mano
            'hotel_id' => ['nullable', Rule::in(Hotel::pluck('id')->all())],
        ]) + ['activo' => $request->boolean('activo')];
    }
}
