<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de una sola fila: los datos del hotel que muestra la app.
        Schema::create('establecimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('eslogan', 150)->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('sitio_web', 150)->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->timestamps();
        });

        DB::table('establecimiento')->insert([
            'nombre' => 'Hotel Buena Vida',
            'eslogan' => 'Club de huéspedes',
            'check_in' => '14:00',
            'check_out' => '12:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('establecimiento');
    }
};
