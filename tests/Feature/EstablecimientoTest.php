<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablecimientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_requiere_sesion(): void
    {
        $this->get('/establecimiento')->assertRedirect('/login');
        $this->put('/establecimiento', ['nombre' => 'X'])->assertRedirect('/login');
    }

    public function test_actualiza_los_datos_y_se_reflejan_en_el_menu(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/establecimiento', [
            'nombre' => 'Hotel Sol de Cusco',
            'eslogan' => 'Huéspedes de siempre',
            'ruc' => '20123456789',
            'telefono' => '+51 984 123 456',
            'email' => 'reservas@sol.pe',
            'sitio_web' => 'https://sol.pe',
            'check_in' => '15:00',
            'check_out' => '11:30',
        ])->assertRedirect(route('establecimiento.edit'))->assertSessionHas('success');

        $hotel = Hotel::actual();
        $this->assertSame('Hotel Sol de Cusco', $hotel->nombre);
        $this->assertSame('15:00', $hotel->hora('check_in'));
        $this->assertSame(1, Hotel::count());
        $this->assertSame('Huéspedes de siempre', $hotel->empresa->nombre_club);
        $this->assertSame('20123456789', $hotel->empresa->ruc);

        $this->get('/dashboard')->assertSee('Sol de Cusco')->assertSee('Huéspedes de siempre');
    }

    public function test_valida_los_campos(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/establecimiento', [
            'nombre' => '',
            'ruc' => '123',
            'telefono' => 'llámame',
            'email' => 'no-es-correo',
            'sitio_web' => 'sol.pe',
            'check_in' => '25:00',
        ])->assertSessionHasErrors(['nombre', 'ruc', 'telefono', 'email', 'sitio_web', 'check_in']);

        $this->assertSame('Hotel Buena Vida', Hotel::actual()->nombre);
    }
}
