<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Fase 3: qué puede hacer cada rol. */
class RolesTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $lima;
    private Hotel $cusco;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lima = Hotel::actual();
        $this->cusco = $this->lima->empresa->hoteles()->create(['nombre' => 'Buena Vida Cusco']);
    }

    private function usuario(string $rol, array $hoteles = []): User
    {
        $u = User::factory()->create(['rol' => $rol, 'empresa_id' => $this->lima->empresa_id]);
        $u->hoteles()->sync(array_map(fn ($h) => $h->id, $hoteles));

        return $u;
    }

    private function nuevoTrabajador(array $extra): array
    {
        return $extra + ['name' => 'Nuevo', 'email' => uniqid() . '@test.local', 'dni' => (string) random_int(10000000, 99999999),
            'password' => 'secreto123', 'password_confirmation' => 'secreto123'];
    }

    public function test_recepcionista_opera_pero_no_gestiona(): void
    {
        $this->actingAs($this->usuario('recepcionista', [$this->lima]));

        foreach (['/dashboard', '/clientes/create', '/clientes/puntos', '/habitaciones/asignar', '/cobrar'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/establecimiento', '/metodos-pago', '/metodos-pago/create', '/9ax1g9', '/plataforma'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->put('/establecimiento', ['nombre' => 'Hackeado'])->assertForbidden();
        $this->get('/dashboard')->assertDontSee('Métodos de pago')->assertDontSee('Nuevo trabajador')->assertSee('Recepción');
    }

    public function test_recepcionista_solo_trabaja_en_sus_hoteles(): void
    {
        $this->actingAs($this->usuario('recepcionista', [$this->cusco]));

        $this->assertSame($this->cusco->id, Hotel::actual()->id);
        $this->post('/hotel-activo', ['hotel_id' => $this->lima->id])->assertNotFound();
        $this->withSession([Hotel::SESION => $this->lima->id])->get('/dashboard')->assertOk();
        $this->assertSame($this->cusco->id, Hotel::actual()->id);
    }

    public function test_personal_sin_hoteles_asignados_no_puede_operar(): void
    {
        $this->actingAs($this->usuario('recepcionista'))->get('/dashboard')->assertForbidden();
    }

    public function test_gerente_edita_su_hotel_pero_no_la_marca(): void
    {
        $this->actingAs($this->usuario('gerente', [$this->lima]));

        $this->get('/establecimiento')->assertOk()->assertSee('solo los cambia su administrador');
        $this->put('/establecimiento', ['nombre' => 'Buena Vida Lima', 'eslogan' => 'Marca pirata', 'ruc' => '20999999999', 'ciudad' => 'Lima'])
            ->assertSessionHasNoErrors();

        $lima = $this->lima->fresh();
        $this->assertSame('Buena Vida Lima', $lima->nombre);
        $this->assertSame('Lima', $lima->ciudad);
        $this->assertSame('Club de huéspedes', $lima->empresa->nombre_club);
        $this->assertNull($lima->empresa->ruc);
        $this->get('/metodos-pago')->assertOk();
    }

    public function test_gerente_solo_registra_recepcionistas_de_sus_hoteles(): void
    {
        $this->actingAs($this->usuario('gerente', [$this->lima]));

        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'admin_empresa']))->assertSessionHasErrors('rol');
        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'recepcionista', 'hoteles' => [$this->cusco->id]]))->assertSessionHasErrors('hoteles.0');
        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'recepcionista']))->assertSessionHasErrors('hoteles');

        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'recepcionista', 'hoteles' => [$this->lima->id], 'email' => 'rosa@test.local']))
            ->assertSessionHasNoErrors();
        $rosa = User::where('email', 'rosa@test.local')->first();
        $this->assertSame('recepcionista', $rosa->rol);
        $this->assertEquals([$this->lima->id], $rosa->hoteles->pluck('id')->all());
    }

    public function test_admin_registra_gerentes_y_administradores(): void
    {
        $this->actingAs($this->usuario('admin_empresa'));

        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'gerente', 'hoteles' => [$this->lima->id, $this->cusco->id], 'email' => 'g@test.local']))
            ->assertSessionHasNoErrors();
        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'admin_empresa', 'email' => 'a@test.local']))->assertSessionHasNoErrors();

        $this->assertCount(2, User::where('email', 'g@test.local')->first()->hoteles);
        $this->assertSame($this->lima->empresa_id, User::where('email', 'a@test.local')->first()->empresa_id);
    }

    public function test_un_admin_no_asigna_hoteles_de_otra_empresa(): void
    {
        $ajeno = Empresa::create(['nombre' => 'Otra'])->hoteles()->create(['nombre' => 'Hotel ajeno']);
        $this->actingAs($this->usuario('admin_empresa'));

        $this->post('/9ax1g9', $this->nuevoTrabajador(['rol' => 'recepcionista', 'hoteles' => [$ajeno->id]]))->assertSessionHasErrors('hoteles.0');
    }

    public function test_super_admin_va_a_plataforma_y_no_opera_hoteles(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'super_admin', 'empresa_id' => null]));

        $this->get('/dashboard')->assertRedirect(route('plataforma'));
        $this->get('/metodos-pago')->assertRedirect(route('plataforma'));
        $this->get('/plataforma')->assertOk()->assertSee('Hotel Buena Vida')->assertSee('Empresas');
    }

    public function test_empresa_suspendida_cierra_la_sesion_de_su_personal(): void
    {
        $usuario = $this->usuario('admin_empresa');
        $this->lima->empresa->update(['estado' => 'suspendida']);

        $this->actingAs($usuario)->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
