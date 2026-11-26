<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\MetodoPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MetodoPagoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_requiere_sesion(): void
    {
        $this->get('/cobrar')->assertRedirect('/login');
        $this->get('/metodos-pago')->assertRedirect('/login');
    }

    public function test_crea_metodo_con_qr(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/metodos-pago', [
            'nombre' => 'Yape', 'numero' => '984 123 456', 'titular' => 'Hotel Buena Vida',
            'qr' => UploadedFile::fake()->image('qr.png', 300, 300), 'activo' => '1',
        ])->assertRedirect(route('metodos-pago.index'));

        $m = MetodoPago::firstOrFail();
        $this->assertTrue($m->activo);
        Storage::disk('public')->assertExists($m->qr_path);
    }

    public function test_rechaza_archivos_que_no_son_imagen_y_qr_faltante(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/metodos-pago', ['nombre' => 'Plin', 'numero' => '999'])
            ->assertSessionHasErrors('qr');
        $this->post('/metodos-pago', [
            'nombre' => 'Plin', 'numero' => 'abc',
            'qr' => UploadedFile::fake()->create('qr.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors(['qr', 'numero']);

        $this->assertSame(0, MetodoPago::count());
    }

    public function test_reemplazar_qr_borra_el_anterior_y_eliminar_borra_el_archivo(): void
    {
        $this->actingAs(User::factory()->create());
        $viejo = UploadedFile::fake()->image('a.png')->store('qr', 'public');
        $m = MetodoPago::create(['hotel_id' => Hotel::actual()->id, 'nombre' => 'Yape', 'numero' => '984', 'qr_path' => $viejo, 'activo' => true]);

        $this->put("/metodos-pago/{$m->id}", ['nombre' => 'Yape', 'numero' => '984', 'qr' => UploadedFile::fake()->image('b.png')]);
        Storage::disk('public')->assertMissing($viejo);
        $nuevo = $m->fresh()->qr_path;
        Storage::disk('public')->assertExists($nuevo);
        $this->assertFalse($m->fresh()->activo); // el checkbox no vino marcado

        $this->delete("/metodos-pago/{$m->id}");
        Storage::disk('public')->assertMissing($nuevo);
        $this->assertSame(0, MetodoPago::count());
    }

    public function test_pantalla_de_cobro_solo_muestra_metodos_activos(): void
    {
        $this->actingAs(User::factory()->create());
        MetodoPago::create(['hotel_id' => Hotel::actual()->id, 'nombre' => 'Yape', 'numero' => '111', 'qr_path' => 'qr/a.png', 'activo' => true]);
        MetodoPago::create(['hotel_id' => Hotel::actual()->id, 'nombre' => 'Plin', 'numero' => '222', 'qr_path' => 'qr/b.png', 'activo' => false]);

        $this->get('/cobrar')->assertOk()->assertSee('Yape')->assertDontSee('Plin');
    }

    public function test_logo_del_establecimiento_se_sube_y_se_quita(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/establecimiento', ['nombre' => 'Hotel Buena Vida', 'logo' => UploadedFile::fake()->image('logo.png')]);
        $logo = Hotel::actual()->empresa->logo_path;
        Storage::disk('public')->assertExists($logo);
        $this->get('/dashboard')->assertSee('storage/' . $logo);

        $this->put('/establecimiento', ['nombre' => 'Hotel Buena Vida', 'quitar_logo' => '1']);
        Storage::disk('public')->assertMissing($logo);
        $this->assertNull(Hotel::actual()->empresa->logo_path);
    }
}
