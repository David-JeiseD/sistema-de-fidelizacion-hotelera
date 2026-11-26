<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Solo un trabajador con sesión puede registrar a otro.
        $this->middleware('auth');
    }

    // Igual que el trait, pero sin iniciar sesión como el usuario recién creado.
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'roles' => array_intersect_key(User::ROLES, array_flip(auth()->user()->rolesAsignables())),
            'hoteles' => Hotel::permitidos()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return redirect()->route('personal.index')->with('success', 'Trabajador registrado: ' . $user->rol_nombre . '.');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'dni' => ['required', 'digits:8', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Solo roles que quien registra puede dar (un gerente no crea administradores)
            'rol' => ['required', Rule::in(auth()->user()->rolesAsignables())],
            // Gerentes y recepcionistas trabajan en hoteles concretos, entre los que quien registra tiene permitidos
            'hoteles' => ['exclude_if:rol,admin_empresa', 'required', 'array', 'min:1'],
            'hoteles.*' => ['integer', Rule::in(Hotel::permitidos()->pluck('id')->all())],
        ], [
            'rol.in' => 'No puedes asignar ese rol.',
            'hoteles.required' => 'Elige al menos un hotel.',
            'hoteles.*.in' => 'Uno de los hoteles no está disponible para ti.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'empresa_id' => auth()->user()->empresa_id, // el personal nuevo entra a la empresa de quien lo registra
            'rol' => $data['rol'],
            'name' => $data['name'],
            'email' => $data['email'],
            'dni' => $data['dni'],
            'password' => Hash::make($data['password']),
        ]);

        if ($data['rol'] !== 'admin_empresa') {
            $user->hoteles()->sync($data['hoteles']);
        }

        return $user;
    }
}
