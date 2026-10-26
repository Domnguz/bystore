@extends('layouts.admin')
@section('title', 'Pedido #'.$pedido->id)
@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Pedido #'.$pedido->id, 'descripcion' => 'Consulta la compra, confirma el pago y gestiona la entrega.'])
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p class="mb-1">{{ $error }}</p>@endforeach</div>@endif
@include('partials.pedido-sync', ['syncUrl' => route('pedidos.show', $pedido->id), 'admin' => true])
<div class="row">
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <h2 class="h5" style="overflow-wrap:anywhere">{{ $pedido->usuario?->name ?? 'Cliente' }} · {{ $pedido->estado_visible }}</h2>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
                <tbody>@foreach($pedido->detalles as $detalle)<tr>
                    <td>{{ $detalle->nombre_producto ?? $detalle->producto?->nombre ?? 'Producto' }}</td><td>{{ $detalle->cantidad }}</td>
                    <td>S/ {{ number_format($detalle->precio, 2) }}</td><td>S/ {{ number_format($detalle->subtotal ?? $detalle->precio * $detalle->cantidad, 2) }}</td>
                </tr>@endforeach</tbody>
            </table></div>
            <p>Productos: S/ {{ number_format($pedido->subtotal_productos ?? $pedido->total, 2) }}</p>
            <p>Envío: {{ $pedido->entrega_cotizada ? 'S/ '.number_format($pedido->costo_envio, 2) : 'Por confirmar' }}</p>
            <strong>{{ $pedido->entrega_cotizada ? 'Total' : 'Subtotal' }}: S/ {{ number_format($pedido->total, 2) }}</strong>
            <hr><h3 class="h5">Datos de entrega</h3>
            <p>{{ $pedido->destinatario }} · {{ $pedido->telefono }} · {{ $pedido->email_contacto }}</p>
            <p>{{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en tienda física' : 'Envío a domicilio' }}</p>
            <p>{{ $pedido->direccion }} {{ $pedido->distrito }} {{ $pedido->provincia }} {{ $pedido->departamento }}</p>
            <p>{{ $pedido->referencia }}</p>
            <p>Comprobante: {{ $pedido->tipo_comprobante }} · {{ $pedido->documento }} · {{ $pedido->razon_social }}</p>
            @if($pedido->reserva_hasta)<p>Reserva hasta {{ $pedido->reserva_hasta->timezone('America/Lima')->format('d/m/Y H:i') }}</p>@endif
        </div></div>
        <div class="card"><div class="card-body"><h3 class="h5">Historial</h3>
            @foreach($pedido->eventos as $evento)<p><strong>{{ $evento->estado }}</strong> · {{ $evento->nota }}<br><small>{{ $evento->created_at->timezone('America/Lima')->format('d/m/Y H:i') }} · Usuario {{ $evento->usuario_id ?? 'Sistema' }}</small></p>@endforeach
        </div></div>
    </div>
    <div class="col-lg-5">
        @if(in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado']))
            <form action="{{ route('pedidos.comprobante-venta', $pedido->id) }}" method="POST" class="card"><div class="card-body">
                @csrf <h3 class="h5">Resumen de compra</h3>
                <p>Genera y comparte con el cliente un PDF informativo con el detalle y total del pedido. No reemplaza la boleta o factura oficial.</p>
                <button class="btn btn-primary">{{ $pedido->comprobante_venta ? 'Actualizar resumen de compra' : 'Generar y adjuntar resumen' }}</button>
            </div></form>
        @endif
        @if($pedido->estado === 'Pendiente')
            @if(($pedido->metodo_entrega === 'envio' || !$pedido->metodo_entrega) && !$pedido->comprobante_pago)
                <form action="{{ route('pedidos.cotizar', $pedido->id) }}" method="POST" class="card"><div class="card-body">
                    @csrf <h3 class="h5">Entrega acordada por WhatsApp</h3>
                    <p>Guarda los datos y la tarifa que acordaste con el comprador. El total actualizado aparecerá en su pedido.</p>
                    @foreach(['direccion' => 'Dirección o punto de entrega acordado', 'departamento' => 'Departamento', 'provincia' => 'Provincia', 'distrito' => 'Distrito', 'referencia' => 'Referencia'] as $campo => $etiqueta)
                        <div class="form-group"><label for="{{ $campo }}">{{ $etiqueta }}</label><input id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $pedido->{$campo}) }}" maxlength="{{ in_array($campo, ['direccion', 'referencia']) ? 255 : 100 }}" class="form-control"></div>
                    @endforeach
                    @if(!$pedido->metodo_entrega)<p>Completa la modalidad de entrega del pedido anterior antes de verificar el pago.</p><label for="metodo_entrega">Entrega acordada</label><select id="metodo_entrega" name="metodo_entrega" required class="form-control mb-3"><option value="retiro">Retiro en tienda</option><option value="envio">Envío a domicilio</option></select>@endif
                    <label for="costo_envio">Tarifa (S/)</label><input id="costo_envio" name="costo_envio" type="number" min="0" max="999999.99" step="0.01" value="{{ old('costo_envio', $pedido->costo_envio) }}" required class="form-control">
                    <button class="btn btn-primary mt-3">Guardar cotización</button>
                </div></form>
            @endif
            <div class="card"><div class="card-body">
                <h3 class="h5">Verificar pago</h3>
                @if(config('tienda.pagos_demo'))<div class="alert alert-warning"><strong>Modo demostración:</strong> Plin y transferencia usan datos de ejemplo. Yape conserva el QR configurado por la tienda; verifica el movimiento real antes de aprobar.</div>@endif
                @if($pedido->comprobante_pago)
                    <div class="alert alert-warning">El cliente envió su constancia. Este pago sigue pendiente de tu confirmación.</div>
                    <dl class="row small">
                        <dt class="col-sm-5">Método de pago</dt><dd class="col-sm-7">{{ \App\Services\MetodosPago::nombre($pedido->metodo_pago) }}</dd>
                        <dt class="col-sm-5">Nombre del pagador</dt><dd class="col-sm-7" style="overflow-wrap:anywhere">{{ $pedido->pagador_nombre ?: 'No indicado' }}</dd>
                        <dt class="col-sm-5">Fecha del pago</dt><dd class="col-sm-7">{{ $pedido->pago_realizado_at?->timezone('America/Lima')->format('d/m/Y H:i') ?: 'No indicada' }}</dd>
                        <dt class="col-sm-5">Constancia recibida</dt><dd class="col-sm-7">{{ $pedido->comprobante_enviado_at?->timezone('America/Lima')->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Total a verificar</dt><dd class="col-sm-7 font-weight-bold">S/ {{ number_format($pedido->total, 2) }}</dd>
                        <dt class="col-sm-5">Referencia enviada</dt><dd class="col-sm-7" style="overflow-wrap:anywhere">{{ $pedido->referencia_pago ?: 'No indicada' }}</dd>
                    </dl>
                    <a href="{{ route('mis-pedidos.comprobante', $pedido->id) }}" target="_blank" rel="noopener" class="d-block mb-3 text-center"><img src="{{ route('mis-pedidos.comprobante', $pedido->id) }}" alt="Constancia enviada por el cliente" class="img-fluid rounded border" style="max-height:320px;object-fit:contain"></a>
                @else
                    <p class="text-muted">El cliente aún no ha enviado una constancia desde su pedido.</p>
                @endif
                @if($pedido->comprobante_pago)<a href="{{ route('mis-pedidos.comprobante', $pedido->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary mb-3">Ver constancia privada</a>@endif
                <p>Comprueba el ingreso real, el importe y el titular en la cuenta correspondiente. La captura enviada no confirma el pago.</p>
                <form action="{{ route('pedidos.aprobar', $pedido->id) }}" method="POST">
                    @csrf
                    <label for="referencia_pago">Referencia del movimiento verificado</label><input id="referencia_pago" name="referencia_pago" maxlength="150" value="{{ old('referencia_pago', $pedido->referencia_pago) }}" required class="form-control">
                    <small class="form-text text-muted">Usa una referencia que identifique este movimiento. Se valida que no se registre en otro pedido.</small>
                    <label class="mt-3"><input type="checkbox" name="pago_verificado" value="1" required> Verifiqué el ingreso del total del pedido.</label>
                    <button class="btn btn-success btn-block" @disabled(!$pedido->entrega_cotizada)>Confirmar pago del cliente</button>
                </form>
                <form action="{{ route('pedidos.rechazar', $pedido->id) }}" method="POST" class="mt-3">
                    @csrf <button class="btn btn-outline-danger btn-block">Cancelar pedido y liberar reserva</button>
                </form>
            </div></div>
        @elseif(in_array($pedido->estado, ['Pagado', 'Enviado']))
            <form action="{{ route('pedidos.estado', $pedido->id) }}" method="POST" class="card"><div class="card-body">
                @csrf
                <input type="hidden" name="estado" value="{{ $pedido->estado === 'Pagado' ? 'Enviado' : 'Entregado' }}">
                @if($pedido->metodo_entrega === 'retiro')
                    <h3 class="h5">Retiro en tienda</h3>
                    <p>{{ $pedido->estado === 'Pagado' ? 'Prepara el pedido y avisa al cliente cuando pueda recogerlo.' : 'Cuando el cliente recoja el pedido, marca el retiro como completado.' }}</p>
                    <button class="btn btn-primary">{{ $pedido->estado === 'Pagado' ? 'Marcar listo para retirar' : 'Marcar como recogido' }}</button>
                @else
                    <h3 class="h5">Avance de entrega</h3>
                    <label for="transportista">Transportista</label><input id="transportista" name="transportista" maxlength="100" value="{{ $pedido->transportista }}" class="form-control">
                    <label for="seguimiento" class="mt-3">Código de seguimiento (opcional)</label><input id="seguimiento" name="seguimiento" maxlength="150" value="{{ $pedido->seguimiento }}" class="form-control">
                    <button class="btn btn-primary mt-3">{{ $pedido->estado === 'Pagado' ? 'Marcar enviado' : 'Marcar entregado' }}</button>
                @endif
            </div></form>
        @elseif($pedido->estado === 'Entregado' && $pedido->metodo_entrega === 'retiro')
            <div class="card border-success"><div class="card-body">
                <h3 class="h5 text-success">Retiro completado</h3>
                <p class="mb-0">El cliente ya recogió su pedido en la tienda.</p>
            </div></div>
        @endif
    </div>
</div>
@stop
