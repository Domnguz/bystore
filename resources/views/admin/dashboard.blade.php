@extends('layouts.admin')

@section('title', 'Ventas | ByteStore')

@section('content_header')
    <div class="bs-admin-heading">
        <p>BYTESTORE / ADMINISTRACIÓN</p>
        <h1>Resumen de ventas</h1>
        <span>Los números de tu tienda, en un solo lugar.</span>
    </div>
@stop

@section('content')
<div class="bs-admin-dashboard">
    <div id="alertaPagosAdmin" class="bs-admin-notice" @if(!$pagosPendientes) hidden @endif role="status">
        <i class="fas fa-receipt" aria-hidden="true"></i>
        <span><strong><span data-pagos-pendientes>{{ $pagosPendientes }}</span> pago(s) por verificar.</strong> Revisa las constancias para confirmar las ventas.</span>
        <a href="{{ route('pedidos.index') }}">Revisar pagos <span aria-hidden="true">→</span></a>
    </div>

    @if($productosStockBajo > 0)
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between" role="status">
            <span><strong>{{ $productosStockBajo }} producto(s)</strong> tienen {{ $limiteStockBajo }} unidades o menos.</span>
            <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="btn btn-sm btn-outline-dark mt-2 mt-sm-0">Revisar stock</a>
        </div>
    @endif

    <form action="{{ route('admin.dashboard') }}" method="GET" class="bs-admin-filters">
        <div>
            <p class="bs-admin-eyebrow">PERÍODO DEL REPORTE</p>
            <fieldset class="bs-admin-periods">
                <legend class="sr-only">Consultar ventas por</legend>
                @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'] as $valor => $nombre)
                    <label><input type="radio" name="periodo" value="{{ $valor }}" @checked($periodo === $valor)><span>{{ $nombre }}</span></label>
                @endforeach
            </fieldset>
        </div>
        <div class="bs-admin-date">
            <label for="fecha-reporte">Fecha de referencia</label>
            <div><input id="fecha-reporte" type="date" name="fecha" value="{{ $fechaSeleccionada }}" required><button type="submit">Consultar <i class="fas fa-arrow-right" aria-hidden="true"></i></button></div>
        </div>
        <div class="bs-admin-range"><strong>{{ $rangoFechas }}</strong><span>Hora de Perú · Semanas de lunes a domingo</span></div>
    </form>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">Selecciona un período y una fecha válidos.</div>
    @endif

    <div class="bs-admin-metrics">
        <article class="bs-admin-metric bs-admin-metric-featured">
            <div class="bs-admin-metric-label">Ventas cobradas <i class="fas fa-wallet" aria-hidden="true"></i></div>
            <strong>S/ {{ number_format((float) $ventasTotales, 2) }}</strong>
            <p>Pagos confirmados en este período</p>
        </article>
        <article class="bs-admin-metric">
            <div class="bs-admin-metric-label">Ventas confirmadas <i class="fas fa-check-circle" aria-hidden="true"></i></div>
            <strong>{{ $ventasConfirmadas }}</strong><p>Pedidos pagados, enviados o entregados</p>
        </article>
        <article class="bs-admin-metric">
            <div class="bs-admin-metric-label">Pedidos recibidos <i class="fas fa-shopping-bag" aria-hidden="true"></i></div>
            <strong>{{ $pedidosRecibidos }}</strong><p>Creados durante el período elegido</p>
        </article>
        <article class="bs-admin-metric">
            <div class="bs-admin-metric-label">Venta promedio <i class="fas fa-chart-line" aria-hidden="true"></i></div>
            <strong>S/ {{ number_format((float) $ticketPromedio, 2) }}</strong><p>Promedio por venta confirmada</p>
        </article>
    </div>

    <div class="bs-admin-report-grid">
        <section class="bs-admin-panel bs-admin-chart-panel" aria-labelledby="titulo-grafica">
            <div class="bs-admin-panel-heading"><div><h2 id="titulo-grafica">{{ ['dia' => 'Ventas del día', 'semana' => 'Ventas de la semana', 'mes' => 'Ventas del mes'][$periodo] }}</h2><p>{{ $periodo === 'dia' ? 'Importe cobrado por hora' : 'Importe cobrado por día' }}</p></div><span class="bs-admin-chart-key">Ventas en soles</span></div>
            @php
                $maximo = max(100, ...array_column($puntosVentas, 'total_centimos'));
                $paso = 650 / count($puntosVentas);
                $anchoBarra = min(48, $paso * 0.58);
                $saltoEtiquetas = $periodo === 'dia' ? 4 : ($periodo === 'mes' ? 5 : 1);
            @endphp
            <div class="bs-admin-chart">
                <svg viewBox="0 0 760 260" role="img" aria-labelledby="descripcion-grafica">
                    <title id="descripcion-grafica">Ventas cobradas, {{ $rangoFechas }}. Total: S/ {{ $ventasTotales }}. El detalle está disponible debajo de la gráfica.</title>
                    @foreach([0, 0.5, 1] as $fraccion)
                        <line x1="88" x2="746" y1="{{ 206 - $fraccion * 168 }}" y2="{{ 206 - $fraccion * 168 }}" stroke="#e8eef3" stroke-dasharray="4 5"/>
                        <text x="76" y="{{ 210 - $fraccion * 168 }}" text-anchor="end" class="bs-admin-axis">{{ number_format($maximo * $fraccion / 100, 2) }}</text>
                    @endforeach
                    @foreach($puntosVentas as $indice => $punto)
                        @php
                            $alto = $punto['total_centimos'] / $maximo * 168;
                            $x = 92 + $indice * $paso + ($paso - $anchoBarra) / 2;
                        @endphp
                        <rect x="{{ $x }}" y="{{ 206 - max(2, $alto) }}" width="{{ $anchoBarra }}" height="{{ max(2, $alto) }}" rx="3" fill="{{ $punto['total_centimos'] ? '#087caa' : '#dbe8ef' }}"><title>{{ $punto['etiqueta'] }}: S/ {{ number_format($punto['total_centimos'] / 100, 2) }} · {{ $punto['pedidos'] }} venta(s)</title></rect>
                        @if($indice % $saltoEtiquetas === 0 || $loop->last)
                            <text x="{{ $x + $anchoBarra / 2 }}" y="232" text-anchor="middle" class="bs-admin-axis">{{ $punto['etiqueta'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
            @if(!$ventasConfirmadas)<p class="bs-admin-empty-note">Aún no hay ventas cobradas en este período.</p>@endif
            <details class="bs-admin-chart-details"><summary>Ver detalle de la gráfica</summary>
                <div class="bs-admin-table-scroll"><table class="bs-admin-table"><caption class="sr-only">Importes cobrados por {{ $periodo === 'dia' ? 'hora' : 'día' }}</caption><thead><tr><th>{{ $periodo === 'dia' ? 'Hora' : 'Día' }}</th><th>Ventas</th><th>Importe</th></tr></thead><tbody>
                @foreach($puntosVentas as $punto)<tr><td>{{ $punto['etiqueta'] }}</td><td>{{ $punto['pedidos'] }}</td><td>S/ {{ number_format($punto['total_centimos'] / 100, 2) }}</td></tr>@endforeach
                </tbody></table></div>
            </details>
            <p class="bs-admin-footnote">Se cuenta la fecha de confirmación del pago. Los importes incluyen envío.</p>
            @if($ventasSinFechaPago)<p class="bs-admin-footnote">{{ $ventasSinFechaPago }} venta(s) antigua(s) sin fecha de pago: se usa la fecha del pedido.</p>@endif
        </section>

        <section class="bs-admin-panel" aria-labelledby="titulo-estados">
            <div class="bs-admin-panel-heading"><div><h2 id="titulo-estados">Estado de los pedidos</h2><p>Recibidos en este período</p></div></div>
            <div class="bs-admin-states">
                @foreach(['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Rechazado'] as $estado)
                    <div><span><span class="bs-admin-state-dot bs-admin-state-{{ strtolower($estado) }}"></span>{{ $estado }}</span><strong>{{ $resumenEstados->get($estado, 0) }}</strong></div>
                @endforeach
            </div>
            <div class="bs-admin-pending"><span>Pendientes en toda la tienda</span><strong>{{ $pedidosPendientes }}</strong><a href="{{ route('pedidos.index') }}">Gestionar pedidos <span aria-hidden="true">→</span></a></div>
        </section>
    </div>

    <section class="bs-admin-panel bs-admin-orders" aria-labelledby="titulo-pedidos">
        <div class="bs-admin-panel-heading"><div><h2 id="titulo-pedidos">Pedidos del período</h2><p>{{ $pedidosRecibidos }} pedido(s) · {{ $rangoFechas }}</p></div><a href="{{ route('pedidos.index') }}">Todos los pedidos <span aria-hidden="true">→</span></a></div>
        <div class="bs-admin-table-scroll"><table class="bs-admin-table"><caption class="sr-only">Pedidos recibidos durante el período elegido</caption>
            <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha (Perú)</th><th>Total</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                @forelse($ultimosPedidos as $pedido)
                    <tr><td class="bs-admin-order-id">#{{ $pedido->id }}</td><td>{{ $pedido->usuario->name ?? 'Sin usuario' }}</td><td>{{ ($pedido->fecha ?? $pedido->created_at)->copy()->setTimezone('America/Lima')->format('d/m/Y H:i') }}</td><td class="bs-admin-order-total">S/ {{ number_format((float) $pedido->total, 2) }}</td><td><span class="bs-admin-status bs-admin-status-{{ strtolower($pedido->estado) }}">{{ $pedido->estado_visible }}</span></td><td><a href="{{ route('pedidos.show', $pedido) }}" aria-label="Ver pedido {{ $pedido->id }}">Ver pedido <span aria-hidden="true">↗</span></a></td></tr>
                @empty
                    <tr><td colspan="6" class="bs-admin-table-empty">No hay pedidos en este período. Puedes consultar otra fecha.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($ultimosPedidos->hasPages())<div class="bs-admin-pagination">{{ $ultimosPedidos->links('pagination::bootstrap-4') }}</div>@endif
    </section>
    <div class="bs-admin-store-summary"><span>En toda la tienda</span><span><strong>{{ $totalProductos }}</strong> productos</span><span><strong>{{ $totalCategorias }}</strong> categorías</span><span><strong>{{ $totalPedidos }}</strong> pedidos</span></div>
</div>
@include('partials.admin-pagos-sync')
@stop
