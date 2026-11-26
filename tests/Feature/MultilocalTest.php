<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\MetodoPago;
use App\Models\Persona;
use App\Models\Recompensa;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultilocalTest extends TestCase
{
    use RefreshDatabase;

    /** Otra empresa con su hotel y un recepcionista. */
    private function otraEmpresa(string $nombre = 'Hostal Sol'): array
    {
        $empresa = Empresa::create(['nombre' => $nombre]);
        $hotel = $empresa->hoteles()->create(['nombre' => $nombre]);
        $usuario = User::factory()->create(['empresa_id' => $empresa->id]);

        return [$empresa, $hotel, $usuario];
    }

    public function test_la_misma_persona_se_reutiliza_entre_empresas(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/clientes/store', ['nombre' => 'Ana Quispe', 'tipo_documento' => 'DNI', 'numero_documento' => '44556677'])
            ->assertSessionHas('success', 'Huésped registrado. Asígnale su primera habitación.');

        [, , $otro] = $this->otraEmpresa();
        $this->actingAs($otro)
            ->post('/clientes/store', ['nombre' => 'Otro nombre', 'tipo_documento' => 'DNI', 'numero_documento' => '44556677'])
            ->assertSessionHas('success', 'La persona ya estaba en la plataforma; quedó inscrita en tu club.');

        $this->assertSame(1, Persona::count());
        $this->assertSame(2, Cliente::count());
        $this->assertSame('Ana Quispe', Persona::first()->nombre); // no se sobrescribe el nombre
    }

    public function test_no_se_inscribe_dos_veces_en_el_mismo_club(): void
    {
        $this->actingAs(User::factory()->create());
        $this->miembro('44556677');

        $this->post('/clientes/store', ['nombre' => 'Ana', 'tipo_documento' => 'DNI', 'numero_documento' => '44556677'])
            ->assertSessionHasErrors(['numero_documento' => 'Esta persona ya es miembro de tu club.']);
    }

    public function test_acepta_ce_y_pasaporte_y_valida_cada_formato(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/clientes/store', ['nombre' => 'John', 'tipo_documento' => 'PASAPORTE', 'numero_documento' => 'x12 345-678'])->assertSessionHasNoErrors();
        $this->post('/clientes/store', ['nombre' => 'Lucía', 'tipo_documento' => 'CE', 'numero_documento' => '001234567'])->assertSessionHasNoErrors();
        $this->post('/clientes/store', ['nombre' => 'X', 'tipo_documento' => 'DNI', 'numero_documento' => '1234567'])->assertSessionHasErrors('numero_documento');
        $this->post('/clientes/store', ['nombre' => 'X', 'tipo_documento' => 'RUC', 'numero_documento' => '12345678'])->assertSessionHasErrors('tipo_documento');

        $this->assertTrue(Persona::where('numero_documento', 'X12345678')->exists()); // normalizado
    }

    public function test_los_puntos_no_cruzan_de_empresa(): void
    {
        $hotelA = Hotel::actual();
        $enA = $this->miembro('44556677', 'Ana', $hotelA);
        $hab = Habitacion::create(['hotel_id' => $hotelA->id, 'nombre' => 'Doble', 'puntos' => 110]);
        Visita::create(['hotel_id' => $hotelA->id, 'cliente_id' => $enA->id, 'habitacion_id' => $hab->id, 'puntos' => 110, 'fecha' => now()]);

        [, $hotelB] = $this->otraEmpresa();
        $enB = $this->miembro('44556677', 'Ana', $hotelB);

        $this->assertSame($enA->persona_id, $enB->persona_id);
        $this->assertSame(110, $enA->fresh()->total_puntos);
        $this->assertSame(0, $enB->fresh()->total_puntos);
    }

    public function test_una_empresa_no_encuentra_miembros_de_otra(): void
    {
        $this->miembro('44556677'); // solo en la empresa original
        [, $hotelB, $usuarioB] = $this->otraEmpresa();
        $habB = Habitacion::create(['hotel_id' => $hotelB->id, 'nombre' => 'Simple', 'puntos' => 40]);

        $this->actingAs($usuarioB);
        $this->get('/clientes/puntos')->assertDontSee('44556677'); // antes de los POST: old() repetiría lo escrito
        $this->get('/habitaciones/asignar')->assertDontSee('44556677');
        $this->post('/clientes/puntos', ['documento' => '44556677'])->assertSessionHasErrors('documento');
        $this->post('/habitaciones/asignar', ['documento' => '44556677', 'habitacion_id' => $habB->id])->assertSessionHasErrors('documento');
    }

    public function test_no_se_usan_habitaciones_ni_qr_de_otro_hotel(): void
    {
        $hotelA = Hotel::actual();
        $habA = Habitacion::create(['hotel_id' => $hotelA->id, 'nombre' => 'Doble', 'puntos' => 110]);
        $qrA = MetodoPago::create(['hotel_id' => $hotelA->id, 'nombre' => 'Yape A', 'numero' => '1', 'qr_path' => 'qr/a.png', 'activo' => true]);

        [, $hotelB, $usuarioB] = $this->otraEmpresa();
        $this->miembro('44556677', 'Ana', $hotelB);

        $this->actingAs($usuarioB);
        $this->post('/habitaciones/asignar', ['documento' => '44556677', 'habitacion_id' => $habA->id])->assertSessionHasErrors('habitacion_id');
        $this->get("/metodos-pago/{$qrA->id}/edit")->assertNotFound();
        $this->delete("/metodos-pago/{$qrA->id}")->assertNotFound();
        $this->get('/cobrar')->assertDontSee('Yape A');
        $this->assertSame(1, MetodoPago::count());
    }

    public function test_recompensa_exclusiva_solo_vale_en_su_hotel(): void
    {
        $hotelA = Hotel::actual();
        $otroHotel = $hotelA->empresa->hoteles()->create(['nombre' => 'Buena Vida Cusco']);
        Recompensa::create(['empresa_id' => $hotelA->empresa_id, 'nombre' => 'Vaso de Jugo', 'puntos' => 40]);
        $exclusiva = Recompensa::create(['empresa_id' => $hotelA->empresa_id, 'hotel_id' => $otroHotel->id, 'nombre' => 'Tour al Valle', 'puntos' => 40]);

        $cliente = $this->miembro('44556677', 'Ana', $hotelA);
        $hab = Habitacion::create(['hotel_id' => $hotelA->id, 'nombre' => 'Doble', 'puntos' => 110]);
        Visita::create(['hotel_id' => $hotelA->id, 'cliente_id' => $cliente->id, 'habitacion_id' => $hab->id, 'puntos' => 110, 'fecha' => now()]);

        // El usuario trabaja en el primer hotel de la empresa (A)
        $this->actingAs(User::factory()->create())
            ->get("/canjes/lista/{$cliente->id}")->assertSee('Vaso de Jugo')->assertDontSee('Tour al Valle');
        $this->post('/canjes/realizar', ['cliente_id' => $cliente->id, 'recompensa_id' => $exclusiva->id])->assertNotFound();
    }
}
