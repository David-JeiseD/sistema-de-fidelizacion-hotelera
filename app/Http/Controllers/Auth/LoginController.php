<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'dni' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    // El DNI se suma a las credenciales (email + DNI + contraseña) y el usuario debe estar activo.
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'dni', 'password') + ['activo' => true];
    }
}
