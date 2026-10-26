<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Str;

class WhatsApp
{
    public static function enlace(string $mensaje = 'Hola, quiero consultar sobre una compra en ByteStore.'): ?string
    {
        $numero = (string) config('tienda.whatsapp', '');
        if (! preg_match('/^9[0-9]{8}$/', $numero)) {
            return null;
        }

        return 'https://wa.me/51'.$numero.'?text='.rawurlencode($mensaje);
    }

    public static function pedido(Pedido $pedido): ?string
    {
        $lineas = ['Hola, quiero coordinar '.($pedido->metodo_entrega === 'retiro' ? 'el retiro' : 'el envío').' de mi pedido #'.$pedido->id.' en ByteStore.'];
        foreach ($pedido->detalles->take(15) as $detalle) {
            $nombre = $detalle->nombre_producto ?? $detalle->producto?->nombre ?? 'Producto';
            $lineas[] = $detalle->cantidad.' × '.Str::limit(preg_replace('/\s+/u', ' ', $nombre), 80);
        }
        if ($pedido->detalles->count() > 15) {
            $lineas[] = 'Y '.($pedido->detalles->count() - 15).' productos más en el pedido.';
        }
        $lineas[] = 'Productos: S/ '.number_format($pedido->subtotal_productos ?? $pedido->total, 2, '.', '');
        $lineas[] = $pedido->entrega_cotizada
            ? 'Total confirmado: S/ '.number_format($pedido->total, 2, '.', '')
            : 'Costo de envío y total final por coordinar.';

        return self::enlace(implode("\n", $lineas));
    }
}
