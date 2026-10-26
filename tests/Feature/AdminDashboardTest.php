<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutVite();
        Notification::fake();
        $this->travelTo(CarbonImmutable::parse('2026-10-05 12:00:00', 'America/Lima'));
        $this->actingAs(User::factory()->create(['rol' => 'admin']));
    }

    private function pedido(string $fecha, string $total = '10.25', string $estado = 'Pagado', ?string $pagado = null): Pedido
    {
        return Pedido::create([
            'usuario_id' => auth()->id(),
            'fecha' => CarbonImmutable::parse($fecha, 'America/Lima')->utc(),
            'total' => $total,
            'estado' => $estado,
            'pagado_at' => $pagado ? CarbonImmutable::parse($pagado, 'America/Lima')->utc() : null,
        ]);
    }

    public function test_day_uses_peru_midnight_and_only_confirmed_sales_with_exact_totals(): void
    {
        $this->pedido('2026-10-04 23:59:59', '90.00');
        $this->pedido('2026-10-05 00:00:00', '0.10', 'Pagado', '2026-10-05 00:00:00');
        $this->pedido('2026-10-05 15:30:00', '0.20', 'Enviado', '2026-10-05 15:30:00');
        $this->pedido('2026-10-05 23:59:59', '10.25', 'Entregado', '2026-10-05 23:59:59');
        $this->pedido('2026-10-06 00:00:00', '80.00');
        $this->pedido('2026-10-05 14:00:00', '100.00', 'Pendiente');
        $this->pedido('2026-10-05 14:00:00', '200.00', 'Rechazado');
        $response = $this->get('/admin?periodo=dia&fecha=2026-10-05')->assertOk();
        $response->assertViewHas('ventasTotales', '10.55')->assertViewHas('ventasConfirmadas', 3)
            ->assertViewHas('pedidosRecibidos', 5)->assertViewHas('ticketPromedio', '3.52');
        $puntos = $response->viewData('puntosVentas');
        $this->assertCount(24, $puntos);
        $this->assertSame(10, $puntos[0]['total_centimos']);
        $this->assertSame(20, $puntos[15]['total_centimos']);
        $this->assertSame(1025, $puntos[23]['total_centimos']);
        $this->assertSame(1055, array_sum(array_column($puntos, 'total_centimos')));
        $this->assertSame(1, $response->viewData('resumenEstados')->get('Rechazado'));
    }

    public function test_sales_use_payment_date_while_received_orders_use_order_date(): void
    {
        $oldOrder = $this->pedido('2026-09-30 10:00:00', '425.00', 'Pagado', '2026-10-05 09:00:00');
        $newOrder = $this->pedido('2026-10-05 10:00:00', '50.00', 'Pagado', '2026-10-06 10:00:00');
        $response = $this->get('/admin?periodo=dia&fecha=2026-10-05')->assertOk()
            ->assertViewHas('ventasTotales', '425.00')->assertViewHas('ventasConfirmadas', 1)
            ->assertViewHas('pedidosRecibidos', 1);
        $this->assertSame([$newOrder->id], $response->viewData('ultimosPedidos')->pluck('id')->all());
        $this->assertSame(42500, $response->viewData('puntosVentas')[9]['total_centimos']);
        $this->assertNotSame($oldOrder->id, $newOrder->id);
    }

    public function test_week_runs_monday_to_sunday_across_a_year_boundary(): void
    {
        $this->pedido('2025-12-28 23:59:59', '100.00');
        $this->pedido('2025-12-29 00:00:00', '10.00');
        $this->pedido('2026-01-04 23:59:59', '20.00');
        $this->pedido('2026-01-05 00:00:00', '200.00');
        $response = $this->get('/admin?periodo=semana&fecha=2026-01-01')->assertOk()
            ->assertViewHas('ventasTotales', '30.00')->assertViewHas('pedidosRecibidos', 2);
        $puntos = $response->viewData('puntosVentas');
        $this->assertCount(7, $puntos);
        $this->assertSame(1000, $puntos[0]['total_centimos']);
        $this->assertSame(2000, $puntos[6]['total_centimos']);
        $this->assertStringContainsString('2025', $response->viewData('rangoFechas'));
        $this->assertStringContainsString('2026', $response->viewData('rangoFechas'));
    }

    public function test_historical_month_supports_leap_year_and_legacy_orders_without_payment_date(): void
    {
        $this->pedido('2024-01-31 23:59:59', '100.00');
        $this->pedido('2024-02-01 00:00:00', '10.00');
        $this->pedido('2024-02-29 23:59:59', '20.00');
        $this->pedido('2024-03-01 00:00:00', '200.00');
        $response = $this->get('/admin?periodo=mes&fecha=2024-02-15')->assertOk()
            ->assertViewHas('ventasTotales', '30.00')->assertViewHas('ventasSinFechaPago', 2)
            ->assertSee('sin fecha de pago');
        $this->assertCount(29, $response->viewData('puntosVentas'));
        $this->assertSame(3000, array_sum(array_column($response->viewData('puntosVentas'), 'total_centimos')));
    }

    public function test_default_is_current_month_in_peru_and_empty_reports_render(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-01 03:00:00', 'UTC'));
        $response = $this->get('/admin')->assertOk()->assertViewHas('periodo', 'mes')
            ->assertViewHas('fechaSeleccionada', '2026-10-31')->assertViewHas('ventasTotales', '0.00')
            ->assertViewHas('ticketPromedio', '0.00')->assertViewHas('ventasConfirmadas', 0)
            ->assertViewHas('pedidosRecibidos', 0)->assertSee('No hay pedidos en este período.');
        $this->assertCount(31, $response->viewData('puntosVentas'));
    }

    public function test_order_list_is_paginated_with_filters_and_global_payment_alert_stays_visible(): void
    {
        foreach (range(1, 12) as $hour) {
            $this->pedido(sprintf('2026-10-05 %02d:00:00', $hour));
        }
        $pending = $this->pedido('2026-09-30 10:00:00', '100.00', 'Pendiente');
        $pending->update(['comprobante_pago' => 'comprobantes/prueba.png']);
        $response = $this->get('/admin?periodo=dia&fecha=2026-10-05')->assertOk()
            ->assertViewHas('pedidosRecibidos', 12)->assertViewHas('pedidosPendientes', 1)
            ->assertViewHas('pagosPendientes', 1)->assertSee('pago(s) de Yape por verificar.');
        $pedidos = $response->viewData('ultimosPedidos');
        $this->assertCount(10, $pedidos);
        $this->assertStringContainsString('periodo=dia', $pedidos->url(2));
        $this->assertStringContainsString('fecha=2026-10-05', $pedidos->url(2));
        $second = $this->get('/admin?periodo=dia&fecha=2026-10-05&page=2')->assertOk();
        $this->assertCount(2, $second->viewData('ultimosPedidos'));
        $this->assertEmpty(array_intersect($pedidos->pluck('id')->all(), $second->viewData('ultimosPedidos')->pluck('id')->all()));
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->getJson('/admin?periodo=anual&fecha=2026-02-30')->assertUnprocessable()
            ->assertJsonValidationErrors(['periodo', 'fecha']);
        $this->getJson('/admin?fecha=not-a-date')->assertUnprocessable()->assertJsonValidationErrors('fecha');
    }

    public function test_customer_cannot_read_sales_report(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'cliente']))
            ->get('/admin?periodo=dia&fecha=2026-10-05')->assertForbidden();
    }
}
