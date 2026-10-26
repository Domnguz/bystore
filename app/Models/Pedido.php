<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id',
        'fecha',
        'total',
        'estado', 'clave_checkout', 'destinatario', 'telefono', 'email_contacto',
        'metodo_entrega', 'departamento', 'provincia', 'distrito', 'direccion', 'referencia',
        'tipo_comprobante', 'documento', 'razon_social', 'subtotal_productos', 'costo_envio',
        'base_imponible', 'igv', 'igv_tasa',
        'entrega_cotizada', 'stock_reservado', 'reserva_hasta', 'pagado_at',
        'referencia_pago', 'comprobante_pago', 'comprobante_enviado_at',
        'pagador_nombre', 'pago_realizado_at',
        'metodo_pago',
        'transportista', 'seguimiento', 'actualizado_por', 'comprobante_venta', 'operacion_yape',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    protected function casts(): array
    {
        return ['fecha' => 'datetime', 'reserva_hasta' => 'datetime', 'pagado_at' => 'datetime', 'comprobante_enviado_at' => 'datetime', 'pago_realizado_at' => 'datetime',
            'total' => 'decimal:2', 'subtotal_productos' => 'decimal:2', 'costo_envio' => 'decimal:2',
            'base_imponible' => 'decimal:2', 'igv' => 'decimal:2', 'igv_tasa' => 'decimal:2',
            'stock_reservado' => 'boolean', 'entrega_cotizada' => 'boolean'];
    }

    public function eventos()
    {
        return $this->hasMany(PedidoEvento::class)->orderBy('id');
    }

    public function getEstadoVisibleAttribute(): string
    {
        if ($this->estado === 'Pendiente' && $this->comprobante_pago) {
            return 'Pago en revisión';
        }
        if ($this->metodo_entrega === 'retiro') {
            if ($this->estado === 'Enviado') {
                return 'Listo para retirar en tienda';
            }
            if ($this->estado === 'Entregado') {
                return 'Recogido en tienda';
            }
        }

        return $this->estado;
    }

    public function getRevisionPagoAttribute(): string
    {
        return hash('sha256', json_encode([$this->estado, $this->total, $this->entrega_cotizada,
            $this->comprobante_pago, $this->comprobante_venta, $this->transportista, $this->seguimiento,
            $this->updated_at?->format('Y-m-d H:i:s.u')]));
    }
}
