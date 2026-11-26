<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Desactivar en vez de borrar: visitas y canjes se borran en cascada con su habitación o
    // recompensa, y el personal deja rastro en el historial.
    public function up(): void
    {
        foreach (['users', 'habitaciones', 'recompensas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('activo')->default(true);
            });
        }
    }

    public function down(): void
    {
        foreach (['users', 'habitaciones', 'recompensas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('activo');
            });
        }
    }
};
