<?php

use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Validation\ValidationException;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tienda:expirar-reservas', function () {
    $liberados = 0;
    Pedido::where('estado', 'Pendiente')->where('stock_reservado', true)
        ->whereNull('comprobante_pago')->where('reserva_hasta', '<', now())
        ->chunkById(100, function ($pedidos) use (&$liberados) {
            foreach ($pedidos as $pedido) {
                try {
                    app(PedidoService::class)->cambiarEstado($pedido->id, 'Rechazado', null, [], 'Reserva vencida sin constancia de pago.');
                    $liberados++;
                } catch (ValidationException $error) {
                    // Un pago o una constancia pueden haberse confirmado mientras se recorría la lista.
                    report($error);
                }
            }
        });
    $this->info('Reservas liberadas: '.$liberados);
})->purpose('Libera reservas vencidas sin pago pendiente de revisión.');

Schedule::command('tienda:expirar-reservas')->everyFiveMinutes()->withoutOverlapping();
