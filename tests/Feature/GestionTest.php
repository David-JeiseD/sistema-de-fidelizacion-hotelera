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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Fase 4: pantallas de gestión (personal, catálogo, hoteles, plataforma). */
class GestionTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotel = Hotel::actual();
        $this->admin = User::factory()->create(['rol' => 'admin_empresa', 'empresa_id' => $this->hotel->empresa_id]);
    }

    private function empleado(string $rol, array $hoteles): User
    {
        $u = User::factory()->create(['rol' => $rol, 'empresa_id' => $this->hotel->empresa_id]);
        $u->hoteles()->sync(collect($hoteles)->pluck('id'));

        return $u;
    }

    // --- Personal ---

    public function test_admin_cambia_rol_y_hoteles_y_desactiva(): void
    {
        $otro = $this->hotel->empresa->hoteles()->create(['nombre' => 'Cusco']);
        $rosa = $this->empleado('recepcionista', [$this->hotel]);
        $this->actingAs($this->admin);

        $this->put("/personal/{$rosa->id}", ['name' => 'Rosa', 'email' => $rosa->email, 'dni' => $rosa->dni, 'rol' => 'gerente', 'hoteles' => [$otro->id]])
            ->assertRedirect(route('personal.index'));
        $this->assertSame('gerente', $rosa->fresh()->rol);
        $this->assertEquals([$otro->id], $rosa->fresh()->hoteles->pluck('id')->all());

        $this->patch("/personal/{$rosa->id}/estado")->assertSessionHas('success');
        $this->assertFalse($rosa->fresh()->activo);
    }

    public function test_desactivado_no_puede_ingresar_y_se_le_cierra_la_sesion(): void
    {
        $rosa = $this->empleado('recepcionista', [$this->hotel]);
        $rosa->update(['password' => Hash::make('secreto123'), 'activo' => false]);

        $this->post('/login', ['email' => $rosa->email, 'dni' => $rosa->dni, 'password' => 'secreto123']);
        $this->assertGuest();

        $this->actingAs($rosa)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_nadie_se_quita_el_acceso_ni_cambia_su_propio_rol(): void
    {
        $this->actingAs($this->admin);

        $this->patch("/personal/{$this->admin->id}/estado")->assertForbidden();
        $this->put("/personal/{$this->admin->id}", ['name' => 'Yo', 'email' => $this->admin->email, 'dni' => $this->admin->dni, 'rol' => 'recepcionista'])
            ->assertSessionHasErrors('rol');
        $this->put("/personal/{$this->admin->id}", ['name' => 'Nuevo nombre', 'email' => $this->admin->email, 'dni' => $this->admin->dni])
            ->assertSessionHasNoErrors(); // sus datos personales sí
        $this->assertSame('admin_empresa', $this->admin->fresh()->rol);
        $this->assertSame('Nuevo nombre', $this->admin->fresh()->name);
    }

    public function test_gerente_solo_ve_y_edita_recepcionistas_de_sus_hoteles(): void
    {
        $cusco = $this->hotel->empresa->hoteles()->create(['nombre' => 'Cusco']);
        $gerente = $this->empleado('gerente', [$this->hotel]);
        $suya = $this->empleado('recepcionista', [$this->hotel, $cusco]);
        $ajena = $this->empleado('recepcionista', [$cusco]);

        $this->actingAs($gerente);
        $this->get('/personal')->assertSee($suya->name)->assertDontSee($ajena->name)->assertDontSee($this->admin->name);
        $this->get("/personal/{$ajena->id}/editar")->assertForbidden();
        $this->get("/personal/{$this->admin->id}/editar")->assertForbidden();

        // Al editarla conserva Cusco aunque el gerente no lo gestione
        $this->put("/personal/{$suya->id}", ['name' => 'Suya', 'email' => $suya->email, 'dni' => $suya->dni, 'rol' => 'recepcionista', 'hoteles' => [$this->hotel->id]])
            ->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing([$this->hotel->id, $cusco->id], $suya->fresh()->hoteles->pluck('id')->all());
    }

    public function test_personal_de_otra_empresa_da_404(): void
    {
        $ajeno = User::factory()->create(['empresa_id' => Empresa::create(['nombre' => 'Otra'])->id]);
        $this->actingAs($this->admin)->get("/personal/{$ajeno->id}/editar")->assertNotFound();
        $this->actingAs($this->admin)->get('/personal')->assertDontSee($ajeno->email);
    }

    // --- Catálogo ---

    public function test_tipos_de_habitacion_con_uso_se_desactivan_no_se_borran(): void
    {
        $this->actingAs($this->admin);
        $this->post('/tipos-habitacion', ['nombre' => 'Suite', 'puntos' => 150, 'activo' => '1'])->assertRedirect();
        $suite = Habitacion::where('nombre', 'Suite')->first();
        $this->assertSame($this->hotel->id, $suite->hotel_id);

        $miembro = $this->miembro('22222222');
        Visita::create(['hotel_id' => $this->hotel->id, 'cliente_id' => $miembro->id, 'habitacion_id' => $suite->id, 'puntos' => 150, 'fecha' => now()]);

        $this->delete("/tipos-habitacion/{$suite->id}")->assertSessionHas('error');
        $this->assertSame(1, Visita::count()); // los puntos del huésped siguen ahí

        $this->put("/tipos-habitacion/{$suite->id}", ['nombre' => 'Suite', 'puntos' => 150]); // sin "activo" = oculto
        $this->get('/habitaciones/asignar')->assertDontSee('Suite');
        $this->post('/habitaciones/asignar', ['documento' => '22222222', 'habitacion_id' => $suite->id])->assertSessionHasErrors('habitacion_id');
    }

    public function test_recompensas_ocultas_no_se_ofrecen_y_las_canjeadas_no_se_borran(): void
    {
        $this->actingAs($this->admin);
        $this->post('/recompensas', ['nombre' => 'Spa', 'puntos' => 10, 'activo' => '1']);
        $spa = Recompensa::where('nombre', 'Spa')->first();
        $miembro = $this->miembro('22222222');
        $hab = Habitacion::create(['hotel_id' => $this->hotel->id, 'nombre' => 'Doble', 'puntos' => 100]);
        Visita::create(['hotel_id' => $this->hotel->id, 'cliente_id' => $miembro->id, 'habitacion_id' => $hab->id, 'puntos' => 100, 'fecha' => now()]);
        Canje::create(['hotel_id' => $this->hotel->id, 'cliente_id' => $miembro->id, 'recompensa_id' => $spa->id, 'puntos' => 10, 'fecha' => now()]);

        $this->delete("/recompensas/{$spa->id}")->assertSessionHas('error');
        $this->put("/recompensas/{$spa->id}", ['nombre' => 'Spa', 'puntos' => 10]); // oculta
        $this->get("/canjes/lista/{$miembro->id}")->assertDontSee('Spa');
        $this->post('/canjes/realizar', ['cliente_id' => $miembro->id, 'recompensa_id' => $spa->id])->assertNotFound();
    }

    public function test_gerente_no_gestiona_recompensas_ni_hoteles(): void
    {
        $this->actingAs($this->empleado('gerente', [$this->hotel]));
        $this->get('/recompensas')->assertForbidden();
        $this->get('/hoteles')->assertForbidden();
        $this->get('/tipos-habitacion')->assertOk();
    }

    // --- Hoteles y plan ---

    public function test_crear_hotel_respeta_el_plan_y_copia_habitaciones(): void
    {
        Habitacion::create(['hotel_id' => $this->hotel->id, 'nombre' => 'Doble', 'puntos' => 110]);
        $this->hotel->empresa->update(['max_hoteles' => 2]);
        $this->actingAs($this->admin);

        $this->post('/hoteles', ['nombre' => 'Cusco', 'zona_horaria' => 'America/Lima', 'copiar_habitaciones' => '1'])
            ->assertRedirect(route('establecimiento.edit'));
        $cusco = Hotel::where('nombre', 'Cusco')->first();
        $this->assertSame($this->hotel->empresa_id, $cusco->empresa_id);
        $this->assertTrue(Habitacion::where('hotel_id', $cusco->id)->where('nombre', 'Doble')->exists());
        $this->assertSame($cusco->id, Hotel::actual()->id); // pasa a trabajar en él

        $this->get('/hoteles/crear')->assertRedirect(route('hoteles.index'));
        $this->post('/hoteles', ['nombre' => 'Arequipa', 'zona_horaria' => 'America/Lima'])->assertForbidden();
        $this->assertSame(2, Hotel::count());
    }

    // --- Plataforma ---

    public function test_super_admin_da_de_alta_una_empresa_completa(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'super_admin', 'empresa_id' => null]));

        $this->post('/plataforma/empresas', [
            'empresa' => 'Sol del Sur', 'ruc' => '20123456789', 'max_hoteles' => 3,
            'hotel' => 'Sol del Sur Arequipa', 'ciudad' => 'Arequipa',
            'admin_nombre' => 'Lucía', 'admin_email' => 'lucia@sol.pe', 'admin_dni' => '45678912',
            'admin_password' => 'secreto123', 'admin_password_confirmation' => 'secreto123',
        ])->assertRedirect(route('plataforma'));

        $empresa = Empresa::where('nombre', 'Sol del Sur')->first();
        $lucia = User::where('email', 'lucia@sol.pe')->first();
        $this->assertSame(3, $empresa->max_hoteles);
        $this->assertSame('admin_empresa', $lucia->rol);
        $this->assertSame($empresa->id, $lucia->empresa_id);

        // Lucía entra y trabaja en su propio hotel, sin ver la empresa original
        $this->post('/logout');
        $this->post('/login', ['email' => 'lucia@sol.pe', 'dni' => '45678912', 'password' => 'secreto123']);
        $this->get('/dashboard')->assertOk()->assertSee('Sol del Sur Arequipa');
        $this->assertSame($empresa->id, Hotel::actual()->empresa_id);
    }

    public function test_super_admin_cambia_plan_y_suspende(): void
    {
        $empresa = $this->hotel->empresa;
        $this->actingAs(User::factory()->create(['rol' => 'super_admin', 'empresa_id' => null]));

        $this->patch("/plataforma/empresas/{$empresa->id}", ['max_hoteles' => 0, 'estado' => 'activa'])->assertSessionHasErrors('max_hoteles');
        $this->patch("/plataforma/empresas/{$empresa->id}", ['max_hoteles' => 5, 'estado' => 'suspendida'])->assertSessionHasNoErrors();

        $this->assertSame(5, $empresa->fresh()->max_hoteles);
        $this->actingAs($this->admin)->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_comando_crea_super_admin(): void
    {
        $this->artisan('plataforma:super-admin', ['email' => 'yo@plataforma.pe', '--dni' => '12345678'])
            ->expectsQuestion('Contraseña (mínimo 8 caracteres, no se muestra)', 'secreto123')
            ->assertSuccessful();

        $yo = User::where('email', 'yo@plataforma.pe')->first();
        $this->assertTrue($yo->esSuperAdmin());
        $this->assertNull($yo->empresa_id);
    }
}
