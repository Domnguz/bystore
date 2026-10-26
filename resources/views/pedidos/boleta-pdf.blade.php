<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Compra #{{ $pedido->id }} | ByteStore</title>
    <style>
        @page { margin: 34px 38px; }
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .top { border-bottom: 2px solid #2563eb; padding-bottom: 16px; }
        .row { width: 100%; }
        .left { width: 58%; vertical-align: top; }
        .right { width: 42%; text-align: right; vertical-align: top; }
        h1 { color: #1d4ed8; font-size: 21px; margin: 0 0 5px; }
        h2 { font-size: 13px; margin: 22px 0 8px; }
        p { line-height: 1.55; margin: 3px 0; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #eff6ff; color: #1e3a8a; text-align: left; }
        th, td { border-bottom: 1px solid #dbe3ee; padding: 8px 6px; }
        .number { text-align: right; white-space: nowrap; }
        .summary { margin: 18px 0 0 auto; width: 48%; }
        .summary td { border: 0; padding: 4px 0; }
        .total { border-top: 2px solid #172033 !important; font-size: 14px; font-weight: bold; }
        .notice { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; margin-top: 22px; padding: 10px; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <table class="top row">
        <tr>
            <td class="left">
                <h1>ByteStore</h1>
                <p>{{ config('tienda.razon_social') ?: 'ByteStore' }}</p>
                @if(config('tienda.ruc'))<p>RUC: {{ config('tienda.ruc') }}</p>@endif
                @if(config('tienda.contacto'))<p>Contacto: {{ config('tienda.contacto') }}</p>@endif
            </td>
            <td class="right">
                <h1>RESUMEN DE COMPRA</h1>
                <p>Pedido #{{ $pedido->id }}</p>
                <p>Fecha: {{ ($pedido->pagado_at ?? $pedido->updated_at ?? $pedido->created_at)->timezone('America/Lima')->format('d/m/Y H:i') }}</p>
                <p>Moneda: Soles (PEN)</p>
            </td>
        </tr>
    </table>

    <h2>Datos del cliente</h2>
    <p><strong>Nombre:</strong> {{ $pedido->destinatario ?: $pedido->usuario?->name }}</p>
    @if($pedido->documento)<p><strong>{{ strlen($pedido->documento) === 11 ? 'RUC' : 'DNI' }}:</strong> {{ $pedido->documento }}</p>@endif
    <p><strong>Correo:</strong> {{ $pedido->email_contacto ?: $pedido->usuario?->email }}</p>
    @if($pedido->direccion)<p><strong>Entrega:</strong> {{ $pedido->direccion }}{{ $pedido->distrito ? ', '.$pedido->distrito : '' }}{{ $pedido->provincia ? ', '.$pedido->provincia : '' }}{{ $pedido->departamento ? ', '.$pedido->departamento : '' }}</p>@endif

    <h2>Detalle de la compra</h2>
    <table>
        <thead><tr><th>Producto</th><th class="number">Cantidad</th><th class="number">P. unitario</th><th class="number">Importe</th></tr></thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->nombre_producto ?: 'Producto' }}</td>
                    <td class="number">{{ $detalle->cantidad }}</td>
                    <td class="number">S/ {{ number_format($detalle->precio, 2) }}</td>
                    <td class="number">S/ {{ number_format($detalle->subtotal ?? $detalle->precio * $detalle->cantidad, 2) }}</td>
                </tr>
            @endforeach
            @if((float) $pedido->costo_envio > 0)
                <tr><td colspan="3">Envío</td><td class="number">S/ {{ number_format($pedido->costo_envio, 2) }}</td></tr>
            @endif
        </tbody>
    </table>

    <table class="summary">
        <tr><td>Base imponible</td><td class="number">S/ {{ number_format($igv['base_imponible'], 2) }}</td></tr>
        <tr><td>IGV ({{ number_format((float) $igv['tasa'], 2) }}%, incluido)</td><td class="number">S/ {{ number_format($igv['igv'], 2) }}</td></tr>
        <tr class="total"><td>Total pagado</td><td class="number">S/ {{ number_format($pedido->total, 2) }}</td></tr>
    </table>

    <div class="notice"><strong>Documento informativo:</strong> este PDF resume tu compra y desglosa el IGV incluido en el total. No es un comprobante de pago electrónico emitido ante SUNAT ni reemplaza la boleta o factura oficial de la tienda.</div>
    <p class="muted" style="margin-top: 14px">Gracias por comprar en ByteStore.</p>
</body>
</html>
