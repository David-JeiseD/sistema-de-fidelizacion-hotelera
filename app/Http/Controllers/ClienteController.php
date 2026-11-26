<?php
namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Hotel;
use App\Models\Persona;
use App\Models\Recompensa;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->merge(['numero_documento' => Persona::normalizar($request->numero_documento)]);
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo_documento' => 'required|in:' . implode(',', array_keys(Persona::TIPOS)),
            'numero_documento' => ['required', function ($attr, $valor, $fail) use ($request) {
                [$nombre, $patron, $regla] = Persona::TIPOS[$request->tipo_documento] ?? [null, null, null];
                if ($patron && ! preg_match($patron, $valor)) {
                    $fail("El {$nombre} debe tener {$regla}.");
                }
            }],
        ]);

        $hotel = Hotel::actual();

        // La persona es global: si otro hotel ya la registró, se reutiliza (sin duplicar ni sobrescribir su nombre).
        $persona = Persona::firstOrCreate(
            ['tipo_documento' => $request->tipo_documento, 'numero_documento' => $request->numero_documento],
            ['nombre' => $request->nombre]
        );
        $membresia = Cliente::firstOrCreate(['empresa_id' => $hotel->empresa_id, 'persona_id' => $persona->id]);

        if (! $membresia->wasRecentlyCreated) {
            throw ValidationException::withMessages(['numero_documento' => 'Esta persona ya es miembro de tu club.']);
        }

        return redirect()->route('habitaciones.formulario', ['documento' => $persona->numero_documento])
            ->with('success', $persona->wasRecentlyCreated
                ? 'Huésped registrado. Asígnale su primera habitación.'
                : 'La persona ya estaba en la plataforma; quedó inscrita en tu club.');
    }

    public function consultarPuntos(Request $request)
    {
        $request->validate(['documento' => 'required|string|max:20']);

        $hotel = Hotel::actual();
        $cliente = self::miembro($request->documento, $hotel);

        $visitas = Visita::with('habitacion', 'hotel')->where('cliente_id', $cliente->id)->get();
        $canjes = Canje::with('recompensa', 'hotel')->where('cliente_id', $cliente->id)->get();
        $total_puntos = $cliente->total_puntos;
        $catalogo = Recompensa::disponiblesEn($hotel);
        $proxima = (clone $catalogo)->where('puntos', '>', $total_puntos)->orderBy('puntos')->first();
        $disponibles = (clone $catalogo)->where('puntos', '<=', $total_puntos)->count();

        return view('clientes.puntos', compact('cliente', 'visitas', 'canjes', 'total_puntos', 'proxima', 'disponibles'));
    }

    public function formularioPuntos()
    {
        $clientes = Cliente::with('persona')->where('empresa_id', Hotel::actual()->empresa_id)->get();
        return view('clientes.consultar_puntos', compact('clientes'));
    }

    // Miembro del club de la empresa del hotel, por número de documento; error de validación si no existe.
    public static function miembro(?string $documento, Hotel $hotel, string $campo = 'documento'): Cliente
    {
        return Cliente::with('persona')
            ->where('empresa_id', $hotel->empresa_id)
            ->porDocumento($documento)
            ->first()
            ?? throw ValidationException::withMessages([$campo => 'No hay ningún miembro del club con ese documento.']);
    }
}
