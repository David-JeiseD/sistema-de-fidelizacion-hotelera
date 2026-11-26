<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Personal de la empresa. El admin gestiona a todos; el gerente solo a los recepcionistas
 * que comparten alguno de sus hoteles. User no tiene scope global (lo usa la autenticación),
 * así que aquí se filtra por empresa a mano.
 */
class PersonalController extends Controller
{
    public function index(Request $request)
    {
        $yo = $request->user();
        $personal = User::with('hoteles')
            ->where('empresa_id', $yo->empresa_id)
            ->orderByDesc('activo')->orderBy('name')
            ->get()
            ->filter(fn (User $u) => $u->is($yo) || $this->puedeGestionar($yo, $u));

        return view('personal.index', compact('personal'));
    }

    public function edit(Request $request, User $usuario)
    {
        $this->autorizar($request->user(), $usuario);

        return view('personal.edit', [
            'usuario' => $usuario->load('hoteles'),
            'roles' => array_intersect_key(User::ROLES, array_flip($request->user()->rolesAsignables())),
            'hoteles' => Hotel::permitidos()->orderBy('nombre')->get(['id', 'nombre']),
            'esYo' => $usuario->is($request->user()),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $yo = $request->user();
        $this->autorizar($yo, $usuario);
        $esYo = $usuario->is($yo);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'dni' => ['required', 'digits:8', Rule::unique('users')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // Nadie cambia su propio rol ni sus hoteles (evita quedarse sin acceso por error)
            'rol' => $esYo ? ['prohibited'] : ['required', Rule::in($yo->rolesAsignables())],
            'hoteles' => $esYo ? ['prohibited'] : ['exclude_if:rol,admin_empresa', 'required', 'array', 'min:1'],
            'hoteles.*' => ['integer', Rule::in(Hotel::permitidos()->pluck('id')->all())],
        ], [
            'rol.in' => 'No puedes asignar ese rol.',
            'hoteles.required' => 'Elige al menos un hotel.',
            'hoteles.*.in' => 'Uno de los hoteles no está disponible para ti.',
        ]);

        $usuario->fill(collect($datos)->only('name', 'email', 'dni', 'rol')->all());
        if (! empty($datos['password'])) {
            $usuario->password = $datos['password']; // el cast 'hashed' la encripta
        }
        $usuario->save();

        if (! $esYo) {
            // Se conservan los hoteles que quien edita no gestiona (un gerente no puede quitar lo que no ve)
            $ajenos = $usuario->hoteles->pluck('id')->diff(Hotel::permitidos()->pluck('id'))->all();
            $usuario->hoteles()->sync($usuario->esAdmin() ? [] : array_merge($datos['hoteles'], $ajenos));
        }

        return redirect()->route('personal.index')->with('success', 'Datos de ' . $usuario->name . ' actualizados.');
    }

    public function estado(Request $request, User $usuario)
    {
        $yo = $request->user();
        $this->autorizar($yo, $usuario);
        // Nadie se quita el acceso a sí mismo; como solo un admin activo gestiona admins,
        // así la empresa nunca se queda sin administrador.
        abort_if($usuario->is($yo), 403, 'No puedes desactivar tu propio acceso.');

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('success', $usuario->name . ($usuario->activo ? ' puede volver a ingresar.' : ' ya no puede ingresar.'));
    }

    private function puedeGestionar(User $yo, User $otro): bool
    {
        if ($otro->empresa_id !== $yo->empresa_id) {
            return false;
        }
        if ($yo->esAdmin()) {
            return true;
        }

        // Gerente: solo recepcionistas que compartan alguno de sus hoteles
        return $yo->tieneRol('gerente') && $otro->tieneRol('recepcionista')
            && $otro->hoteles->pluck('id')->intersect($yo->hoteles->pluck('id'))->isNotEmpty();
    }

    private function autorizar(User $yo, User $usuario): void
    {
        abort_if($usuario->empresa_id !== $yo->empresa_id, 404);
        abort_unless($usuario->is($yo) || $this->puedeGestionar($yo, $usuario), 403, 'No puedes gestionar a este miembro del personal.');
    }
}
