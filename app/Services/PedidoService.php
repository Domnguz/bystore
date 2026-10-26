<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoService
{
    public function cambiarEstado(int $id, string $nuevoEstado, ?int $actor, array $datos = [], string $nota = ''): Pedido
    {
        try {
            return DB::transaction(function () use ($id, $nuevoEstado, $actor, $datos, $nota) {
                $pedido = Pedido::lockForUpdate()->findOrFail($id);
                if ($pedido->estado === $nuevoEstado) {
                    return $pedido; // Reintentar la misma acción no vuelve a consumir o devolver stock.
                }
                $permitidos = ['Pendiente' => ['Pagado', 'Rechazado'], 'Pagado' => ['Enviado'], 'Enviado' => ['Entregado']];
                if (! in_array($nuevoEstado, $permitidos[$pedido->estado] ?? [], true)) {
                    throw ValidationException::withMessages(['estado' => 'Este cambio de estado no está permitido.']);
                }
                if ($nuevoEstado === 'Pagado') {
                    if (! $pedido->entrega_cotizada || ! $pedido->stock_reservado || ($pedido->reserva_hasta && $pedido->reserva_hasta->isPast() && ! $pedido->comprobante_pago)) {
                        throw ValidationException::withMessages(['estado' => 'Confirma el costo de entrega y una reserva vigente antes de registrar el pago.']);
                    }
                    $pedido->pagado_at = now();
                    $pedido->referencia_pago = $datos['referencia_pago'] ?? $pedido->referencia_pago;
                    if (! $pedido->referencia_pago) {
                        throw ValidationException::withMessages(['referencia_pago' => 'Registra la referencia del pago verificado.']);
                    }
                    $pedido->operacion_yape = mb_strtoupper(trim($pedido->referencia_pago));
                    if (Pedido::where('operacion_yape', $pedido->operacion_yape)->whereKeyNot($pedido->id)->exists()) {
                        throw ValidationException::withMessages(['referencia_pago' => 'Esta operación ya fue registrada en otro pedido.']);
                    }
                }
                if ($nuevoEstado === 'Rechazado' && $pedido->stock_reservado) {
                    if ($actor === null && ($pedido->comprobante_pago || ! $pedido->reserva_hasta?->isPast())) {
                        throw ValidationException::withMessages(['pedido' => 'La reserva ya no puede expirar automáticamente.']);
                    }
                    if ($actor === $pedido->usuario_id && $pedido->comprobante_pago) {
                        throw ValidationException::withMessages(['pedido' => 'La constancia debe ser revisada antes de cancelar.']);
                    }
                    $detalles = $pedido->detalles()->orderBy('producto_id')->get();
                    foreach ($detalles as $detalle) {
                        $producto = Producto::lockForUpdate()->findOrFail($detalle->producto_id);
                        $producto->increment('stock', $detalle->cantidad);
                    }
                    $pedido->stock_reservado = false;
                }
                $pedido->fill(array_intersect_key($datos, array_flip(['transportista', 'seguimiento'])));
                $pedido->estado = $nuevoEstado;
                $pedido->actualizado_por = $actor;
                $pedido->save();
                $pedido->eventos()->create(['usuario_id' => $actor, 'estado' => $nuevoEstado,
                    'nota' => $nota ?: 'Estado actualizado.']);

                return $pedido;
            }, 3);
        } catch (UniqueConstraintViolationException $error) {
            throw ValidationException::withMessages(['referencia_pago' => 'Esta operación ya fue registrada en otro pedido.']);
        }
    }
}
