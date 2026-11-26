<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'name',
        'email',
        'dni',
        'rol',
        'activo',
        'password',
    ];

    public const ROLES = [
        'admin_empresa' => 'Administrador',
        'gerente' => 'Gerente',
        'recepcionista' => 'Recepción',
        'super_admin' => 'Plataforma',
    ];

    // Mínimo privilegio por defecto: el rol se asigna explícitamente al registrar.
    protected $attributes = ['rol' => 'recepcionista', 'activo' => true];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Hoteles asignados (solo cuenta para gerentes y recepcionistas).
    public function hoteles()
    {
        return $this->belongsToMany(Hotel::class);
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'super_admin';
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin_empresa';
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function getRolNombreAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }

    // Roles que este usuario puede dar al registrar personal.
    public function rolesAsignables(): array
    {
        return match ($this->rol) {
            'admin_empresa' => ['admin_empresa', 'gerente', 'recepcionista'],
            'gerente' => ['recepcionista'],
            default => [],
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'empresa_id' => 'integer',
        ];
    }
}
