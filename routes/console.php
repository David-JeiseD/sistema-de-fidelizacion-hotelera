<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

// Crea (o actualiza) la cuenta del dueño de la plataforma. Ej.:
//   php artisan plataforma:super-admin tu@correo.com --nombre="Tu Nombre" --dni=12345678
Artisan::command('plataforma:super-admin {email} {--nombre=Administrador de plataforma} {--dni=}', function (string $email) {
    $dni = $this->option('dni') ?: $this->ask('DNI (8 dígitos)');
    $password = $this->secret('Contraseña (mínimo 8 caracteres, no se muestra)');

    $v = Validator::make(compact('email', 'dni', 'password'), [
        'email' => 'required|email',
        'dni' => 'required|digits:8',
        'password' => 'required|min:8',
    ]);
    if ($v->fails()) {
        foreach ($v->errors()->all() as $error) {
            $this->error($error);
        }
        return 1;
    }

    $existente = User::where('email', $email)->first();
    if ($existente && ! $existente->esSuperAdmin() && ! $this->confirm("{$email} ya existe como {$existente->rol_nombre}. ¿Convertirlo en super-admin?")) {
        return 1;
    }

    User::updateOrCreate(['email' => $email], [
        'name' => $this->option('nombre'),
        'dni' => $dni,
        'password' => $password, // cast 'hashed'
        'rol' => 'super_admin',
        'empresa_id' => null,
        'activo' => true,
    ]);

    $this->info("Listo: {$email} es super-admin. Ingresa con su correo, DNI y contraseña; irás directo a Plataforma.");
    return 0;
})->purpose('Crea o actualiza el super-admin de la plataforma');
