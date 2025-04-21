<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Visita;
use App\Models\Canje;
use App\Models\Habitacion;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return view('dashboard', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|max:8|unique:clientes,dni',
        ]);

        // Crear el cliente con total_puntos inicializado en 0
        Cliente::create([
            'nombre' => $request->nombre,
            'dni' => $request->dni,
            'total_puntos' => 0, // Inicializamos los puntos en 0
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente');
    }

 
    public function consultarPuntos(Request $request)
    {
        $cliente = Cliente::where('dni', $request->dni)->firstOrFail();
        $visitas = Visita::where('cliente_id', $cliente->id)->get();
        $canjes = Canje::where('cliente_id', $cliente->id)->get();
        $total_puntos = $cliente->total_puntos;

        return view('clientes.puntos', compact('cliente', 'visitas', 'canjes', 'total_puntos'));
    }

    public function formularioPuntos()
    {
        $clientes = Cliente::all(['id', 'dni', 'nombre']);
        return view('clientes.consultar_puntos', compact('clientes'));
    }

    public function actualizarPuntos($cliente_id, $puntos)
    {
        $cliente = Cliente::findOrFail($cliente_id);
        $cliente->total_puntos += $puntos; // Sumar puntos al cliente
        $cliente->save();

        return redirect()->route('clientes.index')->with('success', 'Puntos actualizados correctamente');
    }
}