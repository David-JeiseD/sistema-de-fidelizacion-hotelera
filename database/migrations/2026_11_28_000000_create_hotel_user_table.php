<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hoteles en los que trabaja cada gerente o recepcionista (el admin de empresa los ve todos).
        Schema::create('hotel_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hoteles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['hotel_id', 'user_id']);
        });

        // Personal que ya existía sin hoteles: se le asignan todos los de su empresa.
        $sinHotel = DB::table('users')->whereIn('rol', ['gerente', 'recepcionista'])->whereNotNull('empresa_id')->get(['id', 'empresa_id']);
        foreach ($sinHotel as $usuario) {
            foreach (DB::table('hoteles')->where('empresa_id', $usuario->empresa_id)->pluck('id') as $hotelId) {
                DB::table('hotel_user')->insert(['hotel_id' => $hotelId, 'user_id' => $usuario->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_user');
    }
};
