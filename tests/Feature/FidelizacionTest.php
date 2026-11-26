<?php

namespace Tests\Feature;

use App\Models\Canje;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Recompensa;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FidelizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_exige_el_dni_correcto(): void
    {
        $user = User::factory()->create(['dni' => '12345678']);

        $this->post('/login', ['email' => $user->email, 'dni' => '99999999', 'password' => 'password']);
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'dni' => '12345678', 'password' => 'password']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registro_requiere_sesion_y_no_cambia_de_usuario(): void
    {
        $datos = ['name' => 'Nuevo', 'email' => 'nuevo@test.local', 'dni' => '87654321',
                  'password' => 'secreto123', 'password_confirmation' => 'secreto123'];

        $this->post('/9ax1g9', $datos)->assertRedirect('/login');
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@test.local']);

        $admin = User::factory()->create();
        $this->actingAs($admin)->post('/9ax1g9', $datos)->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.local']);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_registro_con_dni_duplicado_da_error_de_validacion(): void
    {
        $admin = User::factory()->create(['dni' => '11111111']);

        $this->actingAs($admin)->post('/9ax1g9', [
            'name' => 'Otro', 'email' => 'otro@test.local', 'dni' => '11111111',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertSessionHasErrors('dni');
    }

    public function test_dni_de_cliente_debe_tener_8_digitos(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/clientes/store', ['nombre' => 'X', 'tipo_documento' => 'DNI', 'numero_documento' => 'ab'])
            ->assertSessionHasErrors('numero_documento');

        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_consultar_dni_inexistente_muestra_error_y_no_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/clientes/puntos', ['documento' => '00000000'])
            ->assertSessionHasErrors('documento');
    }

    public function test_asignar_habitacion_valida_y_limita_una_por_dia(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = $this->miembro('22222222');
        $hab = Habitacion::create(['hotel_id' => Hotel::actual()->id, 'nombre' => 'Doble', 'puntos' => 110]);

        $this->post('/habitaciones/asignar', ['documento' => '00000000', 'habitacion_id' => $hab->id])
            ->assertSessionHasErrors('documento');

        $this->post('/habitaciones/asignar', ['documento' => '22222222', 'habitacion_id' => $hab->id]);
        $this->post('/habitaciones/asignar', ['documento' => '22222222', 'habitacion_id' => $hab->id])
            ->assertSessionHas('error');

        $this->assertSame(1, Visita::where('cliente_id', $cliente->id)->count());
        $this->assertSame(110, $cliente->fresh()->total_puntos);
    }

    public function test_canje_descuenta_y_rechaza_si_no_alcanzan_los_puntos(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = $this->miembro('22222222');
        $hab = Habitacion::create(['hotel_id' => Hotel::actual()->id, 'nombre' => 'Doble', 'puntos' => 110]);
        Visita::create(['hotel_id' => Hotel::actual()->id, 'cliente_id' => $cliente->id, 'habitacion_id' => $hab->id, 'puntos' => 110, 'fecha' => now()]);
        $jarra = Recompensa::create(['empresa_id' => Hotel::actual()->empresa_id, 'nombre' => 'Jarra', 'puntos' => 90]);

        $datos = ['cliente_id' => $cliente->id, 'recompensa_id' => $jarra->id];
        $this->post('/canjes/realizar', $datos)->assertSessionHas('success');
        $this->post('/canjes/realizar', $datos)->assertSessionHas('error');

        $this->assertSame(1, Canje::count());
        $this->assertSame(20, $cliente->fresh()->total_puntos);
    }
}
