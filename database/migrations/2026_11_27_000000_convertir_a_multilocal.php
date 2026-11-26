<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1 multi-local (ver docs/PLAN-MULTILOCAL.md):
 * empresas → hoteles; personas globales + membresías (clientes) por empresa;
 * cada tabla operativa pasa a tener dueño. Los datos existentes quedan en la
 * empresa creada a partir del establecimiento actual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('razon_social', 150)->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('nombre_club', 150)->default('Club de huéspedes');
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('max_hoteles')->default(1);
            $table->string('estado', 20)->default('activa'); // activa | suspendida
            $table->timestamps();
        });

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 10); // DNI | CE | PASAPORTE
            $table->string('numero_documento', 20);
            $table->string('nombre', 100);
            $table->timestamps();
            $table->unique(['tipo_documento', 'numero_documento']);
        });

        // --- establecimiento → hoteles, con su empresa ---
        Schema::rename('establecimiento', 'hoteles');
        Schema::table('hoteles', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->cascadeOnDelete();
            $table->string('zona_horaria', 50)->default('America/Lima')->after('check_out');
        });

        foreach (DB::table('hoteles')->orderBy('id')->get() as $hotel) {
            $empresaId = DB::table('empresas')->insertGetId([
                'nombre' => $hotel->nombre,
                'ruc' => $hotel->ruc,
                'nombre_club' => $hotel->eslogan ?: 'Club de huéspedes',
                'logo_path' => $hotel->logo_path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('hoteles')->where('id', $hotel->id)->update(['empresa_id' => $empresaId]);
        }

        if (! DB::table('hoteles')->exists()) {
            $empresaId = DB::table('empresas')->insertGetId(['nombre' => 'Hotel Buena Vida', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('hoteles')->insert(['empresa_id' => $empresaId, 'nombre' => 'Hotel Buena Vida', 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('hoteles', function (Blueprint $table) {
            $table->dropColumn(['ruc', 'eslogan', 'logo_path']); // ahora viven en la empresa
        });
        Schema::table('hoteles', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });

        // Todo lo existente pertenecía al único hotel/empresa.
        $hotel = DB::table('hoteles')->orderBy('id')->first();
        $empresaId = $hotel->empresa_id;

        // --- clientes → personas globales + membresías ---
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('persona_id')->nullable()->after('empresa_id')->constrained('personas')->restrictOnDelete();
        });

        foreach (DB::table('clientes')->orderBy('id')->get() as $cliente) {
            $personaId = DB::table('personas')->insertGetId([
                'tipo_documento' => 'DNI',
                'numero_documento' => $cliente->dni,
                'nombre' => $cliente->nombre,
                'created_at' => $cliente->created_at ?? now(),
                'updated_at' => now(),
            ]);
            DB::table('clientes')->where('id', $cliente->id)->update(['empresa_id' => $empresaId, 'persona_id' => $personaId]);
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['dni']);
        });
        Schema::table('clientes', function (Blueprint $table) {
            // total_puntos estaba obsoleta: el saldo es visitas − canjes.
            $table->dropColumn(['nombre', 'dni', 'total_puntos']);
        });
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
            $table->foreignId('persona_id')->nullable(false)->change();
            $table->unique(['empresa_id', 'persona_id']);
        });

        // --- dueño de cada tabla operativa ---
        foreach (['habitaciones', 'visitas', 'canjes', 'metodos_pago'] as $tabla) {
            $this->agregarDueno($tabla, 'hotel_id', 'hoteles', $hotel->id);
        }
        $this->agregarDueno('recompensas', 'empresa_id', 'empresas', $empresaId);
        Schema::table('recompensas', function (Blueprint $table) {
            // null = vale en todos los hoteles de la empresa
            $table->foreignId('hotel_id')->nullable()->after('empresa_id')->constrained('hoteles')->cascadeOnDelete();
        });

        // --- personal ---
        Schema::table('users', function (Blueprint $table) {
            // null solo para el super-admin de la plataforma
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->cascadeOnDelete();
            $table->string('rol', 20)->default('admin_empresa')->after('dni'); // super_admin | admin_empresa | gerente | recepcionista
        });
        DB::table('users')->update(['empresa_id' => $empresaId]);
    }

    public function down(): void
    {
        throw new RuntimeException('Migración de datos irreversible: restaura el respaldo de storage/backups.');
    }

    private function agregarDueno(string $tabla, string $columna, string $referencia, int $valor): void
    {
        Schema::table($tabla, function (Blueprint $table) use ($columna, $referencia) {
            $table->foreignId($columna)->nullable()->after('id')->constrained($referencia)->cascadeOnDelete();
        });
        DB::table($tabla)->update([$columna => $valor]);
        Schema::table($tabla, function (Blueprint $table) use ($columna) {
            $table->foreignId($columna)->nullable(false)->change();
        });
    }
};
