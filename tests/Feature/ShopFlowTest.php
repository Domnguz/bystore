<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reclamacion;
use App\Models\User;
use App\Services\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutVite();
        Notification::fake();
    }

    private function producto(int $stock = 5): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Componentes']);

        return Producto::create(['categoria_id' => $categoria->id, 'nombre' => 'Memoria de prueba',
            'precio' => '10.25', 'stock' => $stock]);
    }

    private function preparar(User $cliente, Producto $producto, int $cantidad = 1, string $entrega = 'retiro'): array
    {
        $carrito = [$producto->id => ['id' => $producto->id, 'nombre' => $producto->nombre,
            'precio' => $producto->precio, 'imagen' => null, 'cantidad' => $cantidad]];
        $response = $this->actingAs($cliente)->withSession(['carrito' => $carrito])->get('/checkout')->assertOk();

        return ['checkout_token' => $response->viewData('token'), 'destinatario' => 'Cliente de prueba',
            'telefono' => '900000001', 'metodo_entrega' => $entrega,
            'departamento' => 'Lima', 'provincia' => 'Lima', 'distrito' => 'Lima', 'direccion' => 'Dirección de prueba 123'];
    }

    private function pedido(User $cliente, string $entrega = 'retiro'): Pedido
    {
        $datos = $this->preparar($cliente, $this->producto(), 1, $entrega);
        $this->post('/checkout', $datos)->assertRedirect();

        return Pedido::latest('id')->firstOrFail();
    }

    public function test_cliente_no_puede_acceder_a_ninguna_ruta_administrativa(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'cliente']));
        foreach (['/admin', '/admin/productos', '/admin/usuarios', '/admin/categorias', '/admin/pedidos', '/admin/banners', '/admin/tienda', '/admin/reclamaciones'] as $ruta) {
            $this->get($ruta)->assertForbidden();
        }
        $this->post('/admin/usuarios', ['rol' => 'admin'])->assertForbidden();
        $this->post('/admin/pedidos/1/aprobar')->assertForbidden();
    }

    public function test_registro_siempre_crea_cliente_y_login_vuelve_al_checkout(): void
    {
        $captchaId = str_repeat('a', 40);
        $this->withSession(['registration_captchas' => [$captchaId => [
            'hash' => hash('sha256', '123456'), 'expires_at' => now()->addMinutes(10)->timestamp,
        ]]])->post('/registro', ['name' => 'Nuevo cliente', 'apellidos' => 'Pérez García', 'email' => 'nuevo@example.test', 'dni' => '01234567',
            'privacy_accepted' => '1', 'captcha_id' => $captchaId, 'captcha' => '123456',
            'password' => 'Contrasena1234', 'password_confirmation' => 'Contrasena1234', 'rol' => 'admin'])->assertRedirect('/verificar-correo');
        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.test', 'rol' => 'cliente']);
        $cliente = User::where('email', 'nuevo@example.test')->firstOrFail();
        $this->assertNull($cliente->email_verified_at);
        $cliente->markEmailAsVerified();
        $this->post('/logout');
        $this->get('/checkout')->assertRedirect('/login');
        $this->post('/login', ['email' => 'nuevo@example.test', 'password' => 'Contrasena1234'])->assertRedirect('/checkout');
    }

    public function test_login_limita_intentos_repetidos(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => 'no@example.test', 'password' => 'incorrecta'])->assertRedirect();
        }
        $this->post('/login', ['email' => 'no@example.test', 'password' => 'incorrecta'])->assertStatus(429);
    }

    public function test_carrito_no_supera_stock_y_permite_editar_cantidad(): void
    {
        $producto = $this->producto(2);
        $this->postJson('/carrito/agregar/'.$producto->id)->assertOk();
        $this->postJson('/carrito/agregar/'.$producto->id)->assertOk();
        $this->postJson('/carrito/agregar/'.$producto->id)->assertUnprocessable();
        $this->post('/carrito/actualizar/'.$producto->id, ['cantidad' => 1])->assertRedirect();
        $this->assertSame(1, session('carrito')[$producto->id]['cantidad']);
    }

    public function test_checkout_reserva_una_vez_y_repeticion_no_duplica_pedido(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $producto = $this->producto();
        $datos = $this->preparar($cliente, $producto, 2);
        $this->post('/checkout', $datos)->assertRedirect('/mis-pedidos/1');
        $this->post('/checkout', $datos)->assertRedirect('/mis-pedidos/1');
        $this->assertSame(1, Pedido::count());
        $this->assertSame(3, $producto->fresh()->stock);
        $this->assertDatabaseHas('pedidos', ['total' => 20.50, 'estado' => 'Pendiente']);
        $this->assertDatabaseHas('detalle_pedidos', ['nombre_producto' => 'Memoria de prueba', 'cantidad' => 2]);
    }

    public function test_checkout_solo_recoge_entrega_y_comprobante_se_completa_en_yape(): void
    {
        config(['tienda.yape.numero' => '900000001', 'tienda.yape.titular' => 'Titular de prueba']);
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $datos = $this->preparar($cliente, $this->producto());
        $this->get('/checkout')->assertOk()->assertDontSee('name="tipo_comprobante"', false)
            ->assertDontSee('name="documento"', false)->assertDontSee('name="razon_social"', false);
        // El GET renueva el token de revisión del carrito.
        $datos['checkout_token'] = session('checkout_revision.token');
        $this->post('/checkout', [...$datos, 'tipo_comprobante' => 'factura', 'documento' => '12345678901',
            'razon_social' => 'Dato enviado antes de pagar'])->assertSessionHasNoErrors()->assertRedirect();
        $pedido = Pedido::latest('id')->firstOrFail();
        $this->assertNull($pedido->tipo_comprobante);
        $this->assertNull($pedido->documento);
        $this->assertNull($pedido->razon_social);
        $this->get('/mis-pedidos/'.$pedido->id)->assertOk()->assertSee('name="tipo_comprobante"', false)
            ->assertSee('name="documento"', false)->assertSee('name="razon_social"', false);
    }

    public function test_precio_cambiado_exige_revision_y_no_crea_pedido(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $producto = $this->producto();
        $datos = $this->preparar($cliente, $producto);
        $producto->update(['precio' => '12.00']);
        $this->post('/checkout', $datos)->assertSessionHasErrors('carrito');
        $this->assertSame(0, Pedido::count());
        $this->assertSame(5, $producto->fresh()->stock);
    }

    public function test_stock_agotado_despues_de_revision_no_crea_pedido(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $producto = $this->producto(1);
        $datos = $this->preparar($cliente, $producto);
        $producto->update(['stock' => 0]);
        $this->post('/checkout', $datos)->assertSessionHasErrors('carrito');
        $this->assertSame(0, Pedido::count());
    }

    public function test_error_al_guardar_detalle_revierte_pedido_e_inventario(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $producto = $this->producto();
        $datos = $this->preparar($cliente, $producto);
        DetallePedido::creating(fn () => throw new \RuntimeException('Fallo simulado'));
        $this->withoutExceptionHandling();
        try {
            $this->post('/checkout', $datos);
            $this->fail('Se esperaba el fallo simulado.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Fallo simulado', $error->getMessage());
        } finally {
            DetallePedido::flushEventListeners();
        }
        $this->assertSame(0, Pedido::count());
        $this->assertSame(5, $producto->fresh()->stock);
    }

    public function test_aprobar_no_vuelve_a_descontar_y_rechazar_restaura_una_vez(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente);
        $producto = $pedido->detalles->first()->producto;
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);
        $datos = ['referencia_pago' => 'YAPE-PRUEBA-001', 'pago_verificado' => '1'];
        $this->post('/admin/pedidos/'.$pedido->id.'/aprobar', $datos)->assertSessionHasNoErrors();
        $this->post('/admin/pedidos/'.$pedido->id.'/aprobar', $datos)->assertSessionHasNoErrors();
        $this->assertSame(4, $producto->fresh()->stock);
        $this->post('/admin/pedidos/'.$pedido->id.'/rechazar')->assertSessionHasErrors('estado');
        $this->assertSame(4, $producto->fresh()->stock);
        $pendiente = $this->pedido($cliente);
        $reservado = $pendiente->detalles->first()->producto;
        $this->actingAs($admin)->post('/admin/pedidos/'.$pendiente->id.'/rechazar')->assertSessionHasNoErrors();
        $this->post('/admin/pedidos/'.$pendiente->id.'/rechazar')->assertSessionHasNoErrors();
        $this->assertSame(5, $reservado->fresh()->stock);
    }

    public function test_cliente_solo_ve_y_cancela_sus_pedidos(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente);
        $this->get('/mis-pedidos/'.$pedido->id)->assertOk();
        $otro = User::factory()->create(['rol' => 'cliente']);
        $this->actingAs($otro)->get('/mis-pedidos/'.$pedido->id)->assertNotFound();
        $this->post('/mis-pedidos/'.$pedido->id.'/cancelar')->assertNotFound();
        $this->actingAs($cliente)->post('/mis-pedidos/'.$pedido->id.'/cancelar')->assertSessionHasNoErrors();
        $this->assertSame('Rechazado', $pedido->fresh()->estado);
    }

    public function test_envio_se_cotiza_antes_de_registrar_pago(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente, 'envio');
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->post('/admin/pedidos/'.$pedido->id.'/aprobar',
            ['referencia_pago' => 'YAPE-2', 'pago_verificado' => 1])->assertSessionHasErrors('estado');
        $this->post('/admin/pedidos/'.$pedido->id.'/cotizar', ['costo_envio' => '5.50'])->assertSessionHasNoErrors();
        $this->assertSame('15.75', $pedido->fresh()->total);
        $this->assertTrue($pedido->fresh()->entrega_cotizada);
    }

    public function test_envio_puede_coordinarse_por_whatsapp_sin_direccion_inicial(): void
    {
        config(['tienda.whatsapp' => '900000001']);
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $datos = $this->preparar($cliente, $this->producto(), 2, 'envio');
        unset($datos['direccion'], $datos['departamento'], $datos['provincia'], $datos['distrito']);
        $this->post('/checkout', $datos)->assertSessionHasNoErrors()->assertRedirect();
        $pedido = Pedido::latest('id')->firstOrFail();
        $this->assertNull($pedido->direccion);
        $this->assertFalse($pedido->entrega_cotizada);
        $this->get('/mis-pedidos/'.$pedido->id)->assertOk()->assertSee('Coordinar envío por WhatsApp');
        $enlace = WhatsApp::pedido($pedido);
        $this->assertStringStartsWith('https://wa.me/51900000001?text=', $enlace);
        $mensaje = rawurldecode(explode('?text=', $enlace, 2)[1]);
        $this->assertStringContainsString('pedido #'.$pedido->id, $mensaje);
        $this->assertStringContainsString('2 × Memoria de prueba', $mensaje);
        $this->assertStringContainsString('Productos: S/ 20.50', $mensaje);
        $this->assertStringContainsString('total final por coordinar', $mensaje);
        $this->assertStringNotContainsString($cliente->email, $mensaje);

        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->post('/admin/pedidos/'.$pedido->id.'/cotizar', [
            'costo_envio' => '5.50', 'direccion' => 'Dirección acordada por WhatsApp 123',
            'distrito' => 'Lima',
        ])->assertSessionHasNoErrors();
        $pedido->refresh();
        $this->assertSame('26.00', $pedido->total);
        $this->assertSame('Dirección acordada por WhatsApp 123', $pedido->direccion);
        $this->assertTrue($pedido->entrega_cotizada);
        $this->assertStringContainsString('Total confirmado: S/ 26.00', rawurldecode(WhatsApp::pedido($pedido)));
    }

    public function test_whatsapp_sin_numero_valido_no_muestra_enlaces_inventados(): void
    {
        config(['tienda.whatsapp' => '']);
        $this->assertNull(WhatsApp::enlace());
        $this->get('/contacto')->assertOk()->assertDontSee('https://wa.me/');
        config(['tienda.whatsapp' => 'javascript:alert(1)']);
        $this->assertNull(WhatsApp::enlace());
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->put('/admin/tienda', [
            'yape_numero' => '900000001', 'yape_titular' => 'Titular prueba', 'whatsapp' => '123',
        ])->assertSessionHasErrors('whatsapp');
    }

    public function test_constancia_es_privada_y_no_confirma_pago(): void
    {
        Storage::fake('local');
        config(['tienda.yape.numero' => '900000001', 'tienda.yape.titular' => 'Titular prueba', 'tienda.retiro.direccion' => 'Dirección de retiro de prueba']);
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente);
        $this->post('/mis-pedidos/'.$pedido->id.'/pago', [
            'comprobante' => UploadedFile::fake()->createWithContent('constancia.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII=')), 'referencia_pago' => 'YAPE-001',
            'total_confirmado' => $pedido->total,
            'pagador_nombre' => 'Cliente de prueba', 'pago_realizado_at' => now()->timezone('America/Lima')->format('Y-m-d\TH:i'),
            'tipo_comprobante' => 'boleta',
        ])->assertSessionHasNoErrors();
        $pedido->refresh();
        $this->assertSame('Pendiente', $pedido->estado);
        Storage::disk('local')->assertExists($pedido->comprobante_pago);
        $this->get('/mis-pedidos/'.$pedido->id.'/comprobante')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs(User::factory()->create(['rol' => 'cliente']))->get('/mis-pedidos/'.$pedido->id.'/comprobante')->assertNotFound();
        $this->actingAs($cliente)->post('/mis-pedidos/'.$pedido->id.'/cancelar')->assertSessionHasErrors('pedido');
    }

    public function test_expiracion_libera_reserva_una_vez_y_conserva_constancias_por_revisar(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente);
        $producto = $pedido->detalles->first()->producto;
        $pedido->update(['reserva_hasta' => now()->subMinute()]);
        $this->artisan('tienda:expirar-reservas')->assertSuccessful();
        $this->artisan('tienda:expirar-reservas')->assertSuccessful();
        $this->assertSame(5, $producto->fresh()->stock);
        $this->assertSame('Rechazado', $pedido->fresh()->estado);
        $otro = $this->pedido($cliente);
        $otro->update(['reserva_hasta' => now()->subMinute(), 'comprobante_pago' => 'pendiente.jpg']);
        $this->artisan('tienda:expirar-reservas')->assertSuccessful();
        $this->assertSame('Pendiente', $otro->fresh()->estado);
    }

    public function test_recuperacion_de_contrasena_usa_token_y_actualiza_el_acceso(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $token = Password::createToken($cliente);
        $this->post('/restablecer-contrasena', ['email' => $cliente->email, 'token' => $token,
            'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234'])->assertRedirect('/login');
        $this->post('/login', ['email' => $cliente->email, 'password' => 'NuevaClave1234'])->assertRedirect('/');
    }

    public function test_ultimo_admin_no_se_puede_degradar(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->put('/admin/usuarios/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email, 'rol' => 'cliente',
        ])->assertSessionHasErrors('rol');
        $this->assertSame('admin', $admin->fresh()->rol);
    }

    public function test_una_operacion_yape_no_puede_pagar_dos_pedidos(): void
    {
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $primero = $this->pedido($cliente);
        $segundo = $this->pedido($cliente);
        $admin = User::factory()->create(['rol' => 'admin']);
        $datos = ['referencia_pago' => 'MISMA-OPERACION-001', 'pago_verificado' => 1];
        $this->actingAs($admin)->post('/admin/pedidos/'.$primero->id.'/aprobar', $datos)->assertSessionHasNoErrors();
        $this->post('/admin/pedidos/'.$segundo->id.'/aprobar', $datos)->assertSessionHasErrors('referencia_pago');
        $this->assertSame('Pendiente', $segundo->fresh()->estado);
    }

    public function test_comprobante_de_venta_se_adjunta_despues_del_pago_y_es_privado(): void
    {
        Storage::fake('local');
        $cliente = User::factory()->create(['rol' => 'cliente']);
        $pedido = $this->pedido($cliente);
        $admin = User::factory()->create(['rol' => 'admin']);
        $archivo = fn () => UploadedFile::fake()->createWithContent('boleta.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
        $this->actingAs($admin)->post('/admin/pedidos/'.$pedido->id.'/comprobante-venta', ['comprobante_venta' => $archivo()])->assertSessionHasErrors('comprobante_venta');
        $this->post('/admin/pedidos/'.$pedido->id.'/aprobar', ['referencia_pago' => 'YAPE-FACTURA', 'pago_verificado' => 1])->assertSessionHasNoErrors();
        $this->post('/admin/pedidos/'.$pedido->id.'/comprobante-venta', ['comprobante_venta' => $archivo()])->assertSessionHasNoErrors();
        Storage::disk('local')->assertExists($pedido->fresh()->comprobante_venta);
        $this->actingAs($cliente)->get('/mis-pedidos/'.$pedido->id.'/comprobante-venta')->assertOk();
        $this->actingAs(User::factory()->create(['rol' => 'cliente']))->get('/mis-pedidos/'.$pedido->id.'/comprobante-venta')->assertNotFound();
    }

    public function test_busqueda_y_filtros_conservan_resultados_y_orden(): void
    {
        $primero = $this->producto();
        $segundo = $this->producto();
        $segundo->update(['nombre' => 'Memoria económica', 'precio' => '5.00']);
        $agotado = $this->producto(0);
        $response = $this->get('/productos?q=Memoria&min=4&max=11&disponible=1&orden=precio_asc')->assertOk();
        $this->assertSame([$segundo->id, $primero->id], $response->viewData('productos')->pluck('id')->all());
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function test_reclamacion_genera_constancia_privada_y_exige_evidencia_de_respuesta(): void
    {
        $datos = ['nombre' => 'Consumidor', 'documento' => '12345678', 'email' => 'reclamo@example.test',
            'telefono' => '900000001', 'direccion' => 'Dirección de prueba', 'tipo' => 'Reclamo',
            'bien_tipo' => 'Producto', 'bien_descripcion' => 'Memoria', 'monto' => '10.25',
            'detalle' => 'No funciona', 'solicitud' => 'Solicito revisión'];
        $this->post('/reclamaciones', $datos)->assertRedirect();
        $hoja = Reclamacion::firstOrFail();
        $this->get('/reclamaciones/'.$hoja->token)->assertOk()->assertSee('No funciona');
        $this->get('/reclamaciones/'.str_repeat('A', 64))->assertNotFound();
        $this->get('/reclamaciones/1')->assertNotFound();
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->post('/admin/reclamaciones/'.$hoja->id.'/respuesta', ['respuesta' => 'Revisaremos el equipo'])->assertSessionHasErrors('evidencia_respuesta');
        $this->post('/admin/reclamaciones/'.$hoja->id.'/respuesta', ['respuesta' => 'Revisión coordinada',
            'medio_respuesta' => 'Correo electrónico', 'evidencia_respuesta' => 'Correo del 03/10/2026', 'respuesta_entregada' => 1])->assertSessionHasNoErrors();
        $this->assertNotNull($hoja->fresh()->respondido_at);
    }

    public function test_datos_administrativos_y_archivos_invalidos_son_rechazados(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->post('/admin/usuarios', ['name' => 'Cliente', 'email' => 'correo-invalido',
            'rol' => 'admin', 'password' => '123', 'foto' => UploadedFile::fake()->create('programa.php', 1, 'text/plain')])->assertSessionHasErrors(['email', 'password', 'foto']);
        $this->post('/admin/categorias', ['nombre' => ''])->assertSessionHasErrors('nombre');
        $this->get('/admin/tienda')->assertOk();
    }
}
