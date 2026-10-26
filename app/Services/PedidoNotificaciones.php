<?php

namespace App\Services;

use App\Models\Pedido;
use App\Notifications\EstadoPedido;
use Throwable;

class PedidoNotificaciones
{
    public static function enviar(Pedido $pedido): void
    {
        try {
            $pedido->usuario?->notify(new EstadoPedido($pedido));
        } catch (Throwable $error) {
            // Un problema del correo no revierte una compra ya confirmada en la base.
            report($error);
        }
    }
}
