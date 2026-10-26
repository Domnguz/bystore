<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class YapePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutVite();
        Storage::fake('local');
        Storage::fake('public');
        Notification::fake();
        config(['tienda.yape.numero' => '900000001', 'tienda.yape.titular' => 'Titular de prueba', 'tienda.retiro.direccion' => '']);
    }

    private function imagen(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('constancia.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII='));
    }

    private function pedido(User $cliente): Pedido
    {
        $categoria = Categoria::create(['nombre' => 'Componentes']);
        $producto = Producto::create(['categoria_id' => $categoria->id, 'nombre' => 'Memoria de prueba', 'precio' => '25.00', 'stock' => 4]);
        $pedido = Pedido::create(['usuario_id' => $cliente->id, 'fecha' => now(), 'total' => '25.00', 'subtotal_productos' => '25.00',
            'costo_envio' => '0.00', 'estado' => 'Pendiente', 'metodo_entrega' => 'retiro', 'entrega_cotizada' => true,
            'stock_reservado' => true, 'reserva_hasta' => now()->addHours(48), 'destinatario' => 'Cliente de prueba']);
        $pedido->detalles()->create(['producto_id' => $producto->id, 'cantidad' => 1, 'precio' => '25.00', 'subtotal' => '25.00']);

        return $pedido;
    }

    private function datos(): array
    {
        return ['comprobante' => $this->imagen(), 'pagador_nombre' => 'Persona que yapeó',
            'pago_realizado_at' => now()->timezone('America/Lima')->subMinute()->format('Y-m-d\TH:i'),
            'referencia_pago' => 'MOVIMIENTO-001', 'total_confirmado' => '25.00',
            'tipo_comprobante' => 'boleta', 'documento' => '12345678'];
    }

    public function test_constancia_aparece_en_admin_y_confirmacion_llega_al_cliente_sin_descontar_stock_dos_veces(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $pedido = $this->pedido($cliente);
        $this->actingAs($cliente)->get('/mis-pedidos/'.$pedido->id)->assertOk()->assertSee('img/Yape.png')->assertSee('Ya pagué');
        $antes = $this->getJson('/mis-pedidos/'.$pedido->id.'/estado')->assertOk()->assertJsonPath('estado', 'Pendiente')->json('revision');
        $datos = $this->datos();
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $datos)->assertSessionHasNoErrors();
        $pedido->refresh();
        $this->assertSame('Pendiente', $pedido->estado);
        $this->assertSame('Pago en revisión', $pedido->estado_visible);
        $this->assertSame('Persona que yapeó', $pedido->pagador_nombre);
        $this->assertSame('boleta', $pedido->tipo_comprobante);
        $this->assertSame('12345678', $pedido->documento);
        $this->assertSame($datos['pago_realizado_at'], $pedido->pago_realizado_at->timezone('America/Lima')->format('Y-m-d\TH:i'));
        $this->assertNotEquals($antes, $pedido->revision_pago);
        Storage::disk('local')->assertExists($pedido->comprobante_pago);
        $otro = $this->pedido($cliente); // Un pedido más reciente no desplaza un pago por verificar.
        $lista = $this->actingAs($admin)->getJson('/admin/pedidos/actualizaciones')->assertOk()->assertJsonPath('pagosPendientes', 1)->json('lista');
        $this->assertLessThan(strpos($lista, 'Pedido #'.$otro->id), strpos($lista, 'Pedido #'.$pedido->id));
        $this->get('/admin/pedidos/'.$pedido->id)->assertOk()->assertSee('Persona que yapeó')->assertSee('12345678')->assertSee('Confirmar pago del cliente');
        $this->get('/mis-pedidos/'.$pedido->id.'/comprobante')->assertOk();
        $this->post('/admin/pedidos/'.$pedido->id.'/aprobar', ['referencia_pago' => 'MOVIMIENTO-001', 'pago_verificado' => 1])->assertSessionHasNoErrors();
        $this->getJson('/admin/pedidos/actualizaciones?resumen=1')->assertJsonPath('pagosPendientes', 0);
        $this->actingAs($cliente)->getJson('/mis-pedidos/'.$pedido->id.'/estado')->assertOk()->assertJsonPath('estado', 'Pagado');
        $this->get('/mis-pedidos/'.$pedido->id)->assertOk()->assertSee('Pago confirmado')->assertDontSee('data-payment-form', false);
        $this->assertSame(4, $pedido->detalles->first()->producto->stock);
        $this->assertNotNull($pedido->fresh()->pagado_at);
    }

    public function test_rechaza_importe_cambiado_fecha_futura_y_constancia_duplicada(): void
    {
        $pedido = $this->pedido($cliente = User::factory()->create(['rol' => 'cliente']));
        $this->actingAs($cliente)->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'total_confirmado' => '20.00'])->assertSessionHasErrors('comprobante');
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'pago_realizado_at' => now()->timezone('America/Lima')->addHour()->format('Y-m-d\TH:i')])->assertSessionHasErrors('pago_realizado_at');
        $this->assertNull($pedido->fresh()->comprobante_pago);
        $this->assertCount(0, Storage::disk('local')->allFiles('comprobantes-yape'));
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'referencia_pago' => null])->assertSessionHasNoErrors();
        $primera = $pedido->fresh()->comprobante_pago;
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $this->datos())->assertSessionHasErrors('comprobante');
        $this->assertSame($primera, $pedido->fresh()->comprobante_pago);
        $this->assertCount(1, Storage::disk('local')->allFiles('comprobantes-yape'));
        $this->assertSame(1, $pedido->eventos()->count());
    }

    public function test_protege_las_consultas_y_bloquea_pago_sin_cotizacion_o_reserva(): void
    {
        $pedido = $this->pedido($cliente = User::factory()->create(['rol' => 'cliente']));
        $this->actingAs(User::factory()->create(['rol' => 'cliente']))->getJson('/mis-pedidos/'.$pedido->id.'/estado')->assertNotFound();
        $this->getJson('/admin/pedidos/actualizaciones')->assertForbidden();
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $this->datos())->assertNotFound();
        $pedido->update(['metodo_entrega' => 'envio', 'entrega_cotizada' => false]);
        $this->actingAs($cliente)->get('/mis-pedidos/'.$pedido->id)->assertSee('Estamos coordinando tu envío')->assertDontSee('data-payment-form', false);
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $this->datos())->assertSessionHasErrors('comprobante');
        $pedido->update(['entrega_cotizada' => true, 'reserva_hasta' => now()->subMinute()]);
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $this->datos())->assertSessionHasErrors('comprobante');
        $this->assertNull($pedido->fresh()->comprobante_pago);
        $this->assertCount(0, Storage::disk('local')->allFiles('comprobantes-yape'));
    }

    public function test_admin_puede_actualizar_qr_sin_modificar_archivos_reales(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->put('/admin/tienda', ['yape_titular' => 'Titular de prueba', 'yape_numero' => '', 'yape_qr' => $this->imagen()])->assertSessionHasNoErrors();
        Storage::disk('local')->assertExists('tienda.json');
        $configuracion = json_decode(Storage::disk('local')->get('tienda.json'), true);
        Storage::disk('public')->assertExists($configuracion['yape_qr']);
        $this->assertStringStartsWith('pagos/yape/', $configuracion['yape_qr']);
        $this->put('/admin/tienda', ['yape_titular' => 'Titular', 'yape_qr' => UploadedFile::fake()->create('invalido.svg', 1, 'image/svg+xml')])->assertSessionHasErrors('yape_qr');
        $this->assertSame($configuracion, json_decode(Storage::disk('local')->get('tienda.json'), true));
    }

    public function test_factura_se_valida_y_guarda_junto_con_la_constancia(): void
    {
        $pedido = $this->pedido($cliente = User::factory()->create(['rol' => 'cliente']));
        $this->actingAs($cliente);
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'tipo_comprobante' => 'factura'])
            ->assertSessionHasErrors('razon_social');
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'tipo_comprobante' => 'factura',
            'razon_social' => 'Empresa de prueba'])->assertSessionHasErrors('documento');
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'tipo_comprobante' => 'otro'])
            ->assertSessionHasErrors('tipo_comprobante');
        $this->assertNull($pedido->fresh()->tipo_comprobante);
        $this->assertNull($pedido->comprobante_pago);
        $this->assertCount(0, Storage::disk('local')->allFiles('comprobantes-yape'));

        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [...$this->datos(), 'tipo_comprobante' => 'factura',
            'documento' => '20123456789', 'razon_social' => 'Empresa de prueba'])->assertSessionHasNoErrors();
        $pedido->refresh();
        $this->assertSame('factura', $pedido->tipo_comprobante);
        $this->assertSame('20123456789', $pedido->documento);
        $this->assertSame('Empresa de prueba', $pedido->razon_social);
        Storage::disk('local')->assertExists($pedido->comprobante_pago);

        $this->post('/mis-pedidos/'.$pedido->id.'/pago', $this->datos())->assertSessionHasErrors('comprobante');
        $this->assertSame('factura', $pedido->fresh()->tipo_comprobante);
        $this->assertSame('Empresa de prueba', $pedido->razon_social);
    }
}
