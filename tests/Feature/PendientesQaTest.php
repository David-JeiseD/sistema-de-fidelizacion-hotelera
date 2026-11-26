<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Pendientes del QA: zona horaria por hotel, libreta por id, mensajes en español, correo. */
class PendientesQaTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private Habitacion $hab;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotel = Hotel::actual(); // America/Lima (UTC-5)
        $this->hab = Habitacion::create(['hotel_id' => $this->hotel->id, 'nombre' => 'Doble', 'puntos' => 110]);
        $this->actingAs(User::factory()->create(['empresa_id' => $this->hotel->empresa_id]));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_una_estadia_por_dia_usa_la_hora_del_hotel_y_no_utc(): void
    {
        $ana = $this->miembro('11111111');
        // 26/11 a las 11:00 en Lima
        Visita::create(['hotel_id' => $this->hotel->id, 'cliente_id' => $ana->id, 'habitacion_id' => $this->hab->id, 'puntos' => 110, 'fecha' => '2026-11-26 16:00:00']);

        // 26/11 a las 21:00 en Lima = 27/11 02:00 UTC: sigue siendo el mismo día para el hotel
        Carbon::setTestNow('2026-11-27 02:00:00');
        $this->post('/habitaciones/asignar', ['documento' => '11111111', 'habitacion_id' => $this->hab->id])->assertSessionHas('error');

        // 27/11 a la 01:00 en Lima: ya es otro día
        Carbon::setTestNow('2026-11-27 06:00:00');
        $this->post('/habitaciones/asignar', ['documento' => '11111111', 'habitacion_id' => $this->hab->id])->assertSessionHas('success');
        $this->assertSame(2, Visita::count());
    }

    public function test_la_libreta_muestra_la_fecha_local_del_hotel(): void
    {
        $ana = $this->miembro('11111111');
        // 27/11 a las 03:00 UTC = 26/11 a las 22:00 en Lima
        Visita::create(['hotel_id' => $this->hotel->id, 'cliente_id' => $ana->id, 'habitacion_id' => $this->hab->id, 'puntos' => 110, 'fecha' => '2026-11-27 03:00:00']);

        $this->get("/clientes/{$ana->id}/libreta")->assertOk()->assertSee('26/11/2026')->assertDontSee('27/11/2026');
    }

    public function test_consultar_lleva_a_la_libreta_por_id_sin_el_documento_en_la_url(): void
    {
        $ana = $this->miembro('11111111');

        $respuesta = $this->post('/clientes/puntos', ['documento' => '11111111']);
        $respuesta->assertRedirect(route('clientes.libreta', $ana));
        $this->assertStringNotContainsString('11111111', $respuesta->headers->get('Location'));

        $asignar = $this->post('/habitaciones/asignar', ['documento' => '11111111', 'habitacion_id' => $this->hab->id]);
        $asignar->assertRedirect(route('clientes.libreta', $ana));
        $this->get("/habitaciones/asignar?cliente={$ana->id}")->assertSee('value="11111111"', false);
    }

    public function test_libreta_de_otra_empresa_da_404(): void
    {
        $otro = Empresa::create(['nombre' => 'Otra'])->hoteles()->create(['nombre' => 'Otra']);
        $ajeno = $this->miembro('99999999', 'Ajeno', $otro);

        $this->get("/clientes/{$ajeno->id}/libreta")->assertNotFound();
    }

    public function test_mensajes_de_validacion_en_espanol(): void
    {
        $this->post('/clientes/store', ['nombre' => '', 'tipo_documento' => 'DNI', 'numero_documento' => '12345678'])
            ->assertSessionHasErrors(['nombre' => 'El nombre es obligatorio.']);
        $this->post('/metodos-pago', ['nombre' => 'Yape', 'numero' => '999'])
            ->assertSessionHasErrors(['qr' => 'Sube la imagen del código QR.']);

        auth()->logout();
        $this->post('/login', ['email' => 'nadie@test.local', 'dni' => '12345678', 'password' => 'x'])
            ->assertSessionHasErrors(['email' => 'El correo, el DNI o la contraseña no coinciden.']);
    }

    public function test_sin_correo_configurado_no_se_ofrece_recuperar_contrasena(): void
    {
        auth()->logout();
        config(['mail.default' => 'log']);
        $this->get('/login')->assertDontSee('href="' . route('password.request') . '"', false)->assertSee('Pídele al administrador');

        config(['mail.default' => 'smtp']);
        $this->get('/login')->assertSee(route('password.request'));
    }
}
