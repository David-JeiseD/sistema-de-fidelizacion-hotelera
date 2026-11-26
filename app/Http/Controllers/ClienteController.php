<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Visita;
use App\Models\Canje;
use App\Models\Habitacion;
use App\Models\Recompensa;
use Illuminate\Http\Request;

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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'required|digits:8|unique:clientes,dni',
        ]);

        // Crear el cliente con total_puntos inicializado en 0
        Cliente::create([
            'nombre' => $request->nombre,
            'dni' => $request->dni,
            'total_puntos' => 0, // Inicializamos los puntos en 0
        ]);

        return redirect()->route('habitaciones.formulario', ['dni' => $request->dni])
            ->with('success', 'Huésped registrado. Asígnale su primera habitación.');
    }

 
    public function consultarPuntos(Request $request)
    {
        $request->validate(
            ['dni' => 'required|exists:clientes,dni'],
            ['dni.exists' => 'No existe un cliente con ese DNI.']
        );

        $cliente = Cliente::where('dni', $request->dni)->firstOrFail();
        $visitas = Visita::with('habitacion')->where('cliente_id', $cliente->id)->get();
        $canjes = Canje::with('recompensa')->where('cliente_id', $cliente->id)->get();
        $total_puntos = $cliente->total_puntos;
        $proxima = Recompensa::where('puntos', '>', $total_puntos)->orderBy('puntos')->first();
        $disponibles = Recompensa::where('puntos', '<=', $total_puntos)->count();

        return view('clientes.puntos', compact('cliente', 'visitas', 'canjes', 'total_puntos', 'proxima', 'disponibles'));
    }

    public function formularioPuntos()
    {
        $clientes = Cliente::all(['id', 'dni', 'nombre']);
        return view('clientes.consultar_puntos', compact('clientes'));
    }
}