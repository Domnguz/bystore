@extends('layouts.admin')
@section('title', 'Pedidos')
@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Gestión de pedidos', 'descripcion' => 'Revisa los pagos y acompaña cada pedido hasta su entrega.'])
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div id="alertaPagosAdmin" class="alert alert-warning" @if(!$pagosPendientes) hidden @endif role="status">
    <strong><span data-pagos-pendientes>{{ $pagosPendientes }}</span> pago(s) por verificar.</strong> Las constancias nuevas aparecen primero para que puedas revisarlas.
</div>
<form method="GET" action="{{ route('pedidos.index') }}" class="card card-body mb-3" role="search" aria-label="Filtrar pedidos">
    <div class="form-row align-items-end">
        <div class="form-group col-md-6 mb-md-0">
            <label for="buscarPedidoAdmin">Buscar pedido o cliente</label>
            <input id="buscarPedidoAdmin" type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" maxlength="100" class="form-control" placeholder="Número de pedido, nombre o correo">
        </div>
        <div class="form-group col-md-3 mb-md-0">
            <label for="estadoPedidoAdmin">Estado</label>
            <select id="estadoPedidoAdmin" name="estado" class="form-control">
                <option value="">Todos los estados</option>
                @foreach(['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Rechazado'] as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3 mb-md-0">
            <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
            <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>
<p class="text-muted" aria-live="polite"><span data-cantidad-resultados>{{ $cantidadResultados }}</span> pedido(s) encontrados.</p>
<p class="text-muted small">Los pedidos y pagos se actualizan automáticamente cada 15 segundos.</p>
<p class="font-weight-bold" aria-live="polite"><span data-total-pedidos>{{ $totalPedidos }}</span> pedido(s) registrados en la tienda.</p>
<div class="row">
    @foreach(['Pendiente' => 'warning', 'Pagado' => 'success', 'Enviado' => 'info', 'Entregado' => 'primary', 'Rechazado' => 'secondary'] as $estado => $color)
        <div class="col-sm-6 col-xl"><div class="small-box bg-{{ $color }}"><div class="inner"><h3 data-pedido-count="{{ $estado }}">{{ $resumen[$estado] ?? 0 }}</h3><p>{{ $estado }}</p></div></div></div>
    @endforeach
</div>
<div id="listaPedidosAdmin" data-revision="{{ $revision }}">{!! $lista !!}</div>
<p id="estadoPagosAdmin" class="small text-muted" role="status" aria-live="polite"></p>
@include('partials.admin-pagos-sync')
@stop
