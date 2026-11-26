<?php

namespace Tests\Feature;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\MetodoPago;
use App\Models\Recompensa;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 2: ninguna empresa ve ni toca datos de otra, aunque adivine IDs, y cada
 * registro nuevo queda en el hotel activo del usuario.
 */
class AislamientoTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotelA;
    private Hotel $hotelB;
    private User $usuarioA;
    private User $usuarioB;
    private Cliente $miembroA;

    protected function setUp(): void
    {
        parent::setUp();

        // Empresa A (la de la migración) con datos completos
        $this->hotelA = Hotel::actual();
        $this->usuarioA = User::factory()->create(['empresa_id' => $this->hotelA->empresa_id]);
        $this->miembroA = $this->miembro('11111111', 'Ana de A', $this->hotelA);
        $hab = Habitacion::create(['hotel_id' => $this->hotelA->id, 'nombre' => 'Suite A', 'puntos' => 200]);
        Visita::create(['hotel_id' => $this->hotelA->id, 'cliente_id' => $this->miembroA->id, 'habitacion_id' => $hab->id, 'puntos' => 200, 'fecha' => now()]);
        $rec = Recompensa::create(['empresa_id' => $this->hotelA->empresa_id, 'nombre' => 'Vino de A', 'puntos' => 50]);
        Canje::create(['hotel_id' => $this->hotelA->id, 'cliente_id' => $this->miembroA->id, 'recompensa_id' => $rec->id, 'puntos' => 50, 'fecha' => now()]);
        MetodoPago::create(['hotel_id' => $this->hotelA->id, 'nombre' => 'Yape de A', 'numero' => '1', 'qr_path' => 'qr/a.png', 'activo' => true]);

        // Empresa B, vacía
        $empresaB = Empresa::create(['nombre' => 'Hostal B']);
        $this->hotelB = $empresaB->hoteles()->create(['nombre' => 'Hostal B']);
        $this->usuarioB = User::factory()->create(['empresa_id' => $empresaB->id]);
    }

    public function test_las_consultas_eloquent_solo_ven_la_propia_empresa(): void
    {
        $this->actingAs($this->usuarioB);

        foreach ([Cliente::class, Visita::class, Canje::class, Habitacion::class, Recompensa::class, MetodoPago::class] as $modelo) {
            $this->assertSame(0, $modelo::count(), "$modelo filtra datos de otra empresa");
        }
        $this->assertEquals([$this->hotelB->id], Hotel::pluck('id')->all());
        $this->assertNull(Cliente::find($this->miembroA->id));
    }

    public function test_sin_sesion_y_desde_consola_no_se_filtra(): void
    {
        $this->assertSame(2, Hotel::count());
        $this->assertSame(1, Cliente::count());
    }

    public function test_las_pantallas_no_muestran_datos_ajenos(): void
    {
        $this->actingAs($this->usuarioB);

        foreach (['/dashboard', '/clientes/puntos', '/habitaciones/asignar', '/cobrar', '/metodos-pago'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('Ana de A')->assertDontSee('11111111')
                ->assertDontSee('Suite A')->assertDontSee('Vino de A')->assertDontSee('Yape de A');
        }
        $this->get('/dashboard')->assertSee('data-count="0"', false);
    }

    public function test_adivinar_ids_ajenos_no_sirve(): void
    {
        $this->actingAs($this->usuarioB);
        $qr = MetodoPago::withoutGlobalScopes()->first();
        $rec = Recompensa::withoutGlobalScopes()->first();

        $this->get("/canjes/lista/{$this->miembroA->id}")->assertRedirect(route('clientes.puntos'));
        $this->get("/metodos-pago/{$qr->id}/edit")->assertNotFound();
        $this->put("/metodos-pago/{$qr->id}", ['nombre' => 'Hackeado', 'numero' => '999'])->assertNotFound();
        $this->delete("/metodos-pago/{$qr->id}")->assertNotFound();
        $this->post('/canjes/realizar', ['cliente_id' => $this->miembroA->id, 'recompensa_id' => $rec->id])->assertNotFound();
        $this->post('/hotel-activo', ['hotel_id' => $this->hotelA->id])->assertNotFound();

        $this->assertSame('Yape de A', $qr->fresh()->nombre);
        $this->assertSame(1, Canje::withoutGlobalScopes()->count());
    }

    public function test_una_sesion_manipulada_no_salta_a_otra_empresa(): void
    {
        $this->actingAs($this->usuarioB)->withSession([Hotel::SESION => $this->hotelA->id])
            ->get('/habitaciones/asignar')->assertOk()->assertDontSee('Suite A');

        $this->assertSame($this->hotelB->id, Hotel::actual()->id);
    }

    public function test_cadena_cambia_de_hotel_y_comparte_puntos_entre_locales(): void
    {
        $cusco = $this->hotelA->empresa->hoteles()->create(['nombre' => 'Buena Vida Cusco']);
        $habCusco = Habitacion::create(['hotel_id' => $cusco->id, 'nombre' => 'Vista al valle', 'puntos' => 70]);
        $this->actingAs($this->usuarioA);

        $this->get('/dashboard')->assertSee('Buena Vida Cusco'); // aparece el selector
        $this->post('/hotel-activo', ['hotel_id' => $cusco->id])->assertSessionHas(Hotel::SESION, $cusco->id);

        // Lo que se registra ahora queda en Cusco, sin indicar el hotel
        $this->get('/habitaciones/asignar')->assertSee('Vista al valle')->assertDontSee('Suite A');
        $this->post('/habitaciones/asignar', ['documento' => '11111111', 'habitacion_id' => $habCusco->id])->assertSessionHas('success');

        $visita = Visita::latest('id')->first();
        $this->assertSame($cusco->id, $visita->hotel_id);
        // 200 (Lima) − 50 (canje) + 70 (Cusco): una sola libreta para toda la cadena
        $this->assertSame(220, $this->miembroA->fresh()->total_puntos);
        $this->post('/clientes/puntos', ['documento' => '11111111'])->assertSee('Estadía · Buena Vida Cusco');
    }

    public function test_los_registros_nuevos_toman_empresa_y_hotel_del_usuario(): void
    {
        $this->actingAs($this->usuarioB);

        $rec = Recompensa::create(['nombre' => 'Desayuno B', 'puntos' => 30]);
        $hab = Habitacion::create(['nombre' => 'Simple B', 'puntos' => 20]);

        $this->assertSame($this->hotelB->empresa_id, $rec->empresa_id);
        $this->assertSame($this->hotelB->id, $hab->hotel_id);
    }

    public function test_super_admin_sin_empresa_ve_todo(): void
    {
        $this->actingAs(User::factory()->create(['empresa_id' => null, 'rol' => 'super_admin']));

        $this->assertSame(2, Hotel::count());
        $this->assertSame(1, Cliente::count());
    }
}
