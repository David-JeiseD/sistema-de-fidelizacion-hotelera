<?php

namespace Tests\Feature;

use App\Models\Canje;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Recompensa;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Fase 5: reportes consolidados y por hotel. */
class ReportesTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $lima;
    private Hotel $cusco;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-20 15:00:00'); // 20/11 10:00 en Lima

        $this->lima = Hotel::actual();
        $this->cusco = $this->lima->empresa->hoteles()->create(['nombre' => 'Cusco']);
        $habLima = Habitacion::create(['hotel_id' => $this->lima->id, 'nombre' => 'Doble Lima', 'puntos' => 100]);
        $habCusco = Habitacion::create(['hotel_id' => $this->cusco->id, 'nombre' => 'Suite Cusco', 'puntos' => 50]);
        $vino = Recompensa::create(['empresa_id' => $this->lima->empresa_id, 'nombre' => 'Vino', 'puntos' => 40]);

        $ana = $this->miembro('11111111', 'Ana');
        $luis = $this->miembro('22222222', 'Luis');

        $estadia = fn ($cliente, $hab, $hotel, $fecha) => Visita::create(['hotel_id' => $hotel->id, 'cliente_id' => $cliente->id, 'habitacion_id' => $hab->id, 'puntos' => $hab->puntos, 'fecha' => $fecha]);
        // Noviembre: Ana 2 en Lima + 1 en Cusco, Luis 1 en Lima
        $estadia($ana, $habLima, $this->lima, '2026-11-03 15:00:00');
        $estadia($ana, $habLima, $this->lima, '2026-11-10 15:00:00');
        $estadia($ana, $habCusco, $this->cusco, '2026-11-12 15:00:00');
        $estadia($luis, $habLima, $this->lima, '2026-11-15 15:00:00');
        // Octubre (periodo anterior): 1 estadía
        $estadia($luis, $habLima, $this->lima, '2026-10-20 15:00:00');
        // 1/11 a las 02:00 UTC = 31/10 21:00 en Lima: cuenta en octubre, no en noviembre
        $estadia($luis, $habCusco, $this->cusco, '2026-11-01 02:00:00');
        Canje::create(['hotel_id' => $this->cusco->id, 'cliente_id' => $ana->id, 'recompensa_id' => $vino->id, 'puntos' => 40, 'fecha' => '2026-11-12 16:00:00']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin_empresa', 'empresa_id' => $this->lima->empresa_id]);
    }

    public function test_consolidado_de_la_cadena_en_hora_local(): void
    {
        $vista = $this->actingAs($this->admin())->get('/reportes')->assertOk()->viewData('kpis');

        $this->assertSame([4, 350, 1, 2], array_column($vista, 1)); // estadías, puntos, canjes, huéspedes de noviembre
        $this->assertSame([2, 150, 0, 1], array_column($vista, 2)); // octubre (incluye la de las 21:00 del 31/10)
    }

    public function test_desglose_por_hotel_y_rankings(): void
    {
        $r = $this->actingAs($this->admin())->get('/reportes');

        $porHotel = $r->viewData('porHotel')->keyBy('nombre');
        $this->assertSame(3, $porHotel['Hotel Buena Vida']['estadias']);
        $this->assertSame(1, $porHotel['Cusco']['canjes']);
        $this->assertSame(['Vino' => 1], $r->viewData('topRecompensas')->all());
        $this->assertSame('Ana', $r->viewData('topHuespedes')->first()['nombre']);
        $this->assertSame(11, $r->viewData('tasaCanje')); // 40 canjeados de 350 otorgados
    }

    public function test_filtro_por_hotel_y_periodo(): void
    {
        $this->actingAs($this->admin());

        $cusco = $this->get("/reportes?hotel={$this->cusco->id}")->viewData('kpis');
        $this->assertSame([1, 50, 1, 1], array_column($cusco, 1));

        $anterior = $this->get('/reportes?periodo=mes_anterior')->viewData('kpis');
        $this->assertSame(2, $anterior[0][1]);

        $tendencia = $this->get('/reportes')->viewData('tendencia');
        $this->assertCount(12, $tendencia);
        $this->assertSame(350, $tendencia->last()['otorgados']);
        $this->assertSame(150, $tendencia[10]['otorgados']); // octubre
    }

    public function test_gerente_solo_ve_sus_hoteles_y_recepcion_no_entra(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente', 'empresa_id' => $this->lima->empresa_id]);
        $gerente->hoteles()->sync([$this->cusco->id]);

        $kpis = $this->actingAs($gerente)->get("/reportes?hotel={$this->lima->id}")->viewData('kpis'); // pide Lima: se ignora
        $this->assertSame([1, 50, 1, 1], array_column($kpis, 1));

        $recepcion = User::factory()->create(['rol' => 'recepcionista', 'empresa_id' => $this->lima->empresa_id]);
        $recepcion->hoteles()->sync([$this->lima->id]);
        $this->actingAs($recepcion)->get('/reportes')->assertForbidden();
    }

    public function test_otra_empresa_no_ve_estos_datos(): void
    {
        $otra = Empresa::create(['nombre' => 'Otra']);
        $otra->hoteles()->create(['nombre' => 'Otra']);
        $admin = User::factory()->create(['rol' => 'admin_empresa', 'empresa_id' => $otra->id]);

        $kpis = $this->actingAs($admin)->get('/reportes')->assertOk()->assertDontSee('Ana')->viewData('kpis');
        $this->assertSame([0, 0, 0, 0], array_column($kpis, 1));
    }
}
