@extends('layouts.admin')
@section('title', 'Datos de tienda')
@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Datos de tienda', 'descripcion' => 'Mantén al día la información del negocio y los datos de pago.'])
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form action="{{ route('admin.tienda.update') }}" method="POST" enctype="multipart/form-data" class="card"><div class="card-body">
    @csrf @method('PUT')
    <p>El cliente verá solo los métodos con datos completos. Verifica cada número y cuenta antes de guardarlos.</p>
    <div class="alert alert-warning"><strong>Modo exposición:</strong> marca esta opción si usas datos de muestra. Se mostrará un aviso al comprador; el QR configurado de Yape seguirá visible.</div>
    <input type="hidden" name="pagos_demo" value="0">
    <div class="form-group"><label class="d-flex align-items-start" for="pagos_demo"><input id="pagos_demo" name="pagos_demo" type="checkbox" value="1" class="mr-2 mt-1" @checked(old('pagos_demo', config('tienda.pagos_demo')))> <span><strong>Usar datos de muestra para la exposición</strong><br><small class="text-muted">El comprador verá un aviso. El QR configurado de Yape se mantiene visible; Plin y transferencia pueden usar datos ficticios.</small></span></label></div>
    <h2 class="h5 mt-3">Yape</h2>
    <div class="row">
        @foreach(['yape_numero' => ['Número Yape', config('tienda.yape.numero')], 'yape_titular' => ['Titular Yape', config('tienda.yape.titular')], 'direccion' => ['Dirección exacta de retiro', config('tienda.retiro.direccion')], 'contacto' => ['Teléfono o correo de contacto', config('tienda.contacto')], 'razon_social' => ['Razón social del negocio', config('tienda.razon_social')], 'ruc' => ['RUC del negocio', config('tienda.ruc')]] as $campo => [$etiqueta, $valor])
            <div class="col-md-6 form-group"><label for="{{ $campo }}">{{ $etiqueta }}</label><input id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $valor) }}" @required($campo === 'yape_titular') maxlength="255" class="form-control"></div>
        @endforeach
    </div>
    <h2 class="h5 mt-4">Plin</h2>
    <div class="row">
        <div class="col-md-6 form-group"><label for="plin_numero">Número Plin</label><input id="plin_numero" name="plin_numero" value="{{ old('plin_numero', config('tienda.plin.numero')) }}" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" class="form-control"><small class="form-text text-muted">Número peruano de 9 dígitos.</small></div>
        <div class="col-md-6 form-group"><label for="plin_titular">Titular Plin</label><input id="plin_titular" name="plin_titular" value="{{ old('plin_titular', config('tienda.plin.titular')) }}" maxlength="150" class="form-control"></div>
    </div>
    <h2 class="h5 mt-3">Transferencia bancaria</h2>
    <div class="row">
        @foreach(['transferencia_banco' => ['Banco', config('tienda.transferencia.banco')], 'transferencia_titular' => ['Titular de la cuenta', config('tienda.transferencia.titular')], 'transferencia_cuenta' => ['Número de cuenta', config('tienda.transferencia.cuenta')], 'transferencia_cci' => ['CCI (20 dígitos)', config('tienda.transferencia.cci')]] as $campo => [$etiqueta, $valor])
            <div class="col-md-6 form-group"><label for="{{ $campo }}">{{ $etiqueta }}</label><input id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $valor) }}" maxlength="{{ $campo === 'transferencia_cci' ? 20 : 150 }}" @if($campo === 'transferencia_cci') inputmode="numeric" pattern="[0-9]{20}" @endif class="form-control"></div>
        @endforeach
    </div>
    <div class="row align-items-center mb-4">
        <div class="col-md-4 text-center mb-3 mb-md-0">
            @if($qr = \App\Services\PagoYape::qrUrl())
                <img src="{{ $qr }}" alt="QR de Yape del negocio" style="width:100%;max-width:220px;height:auto" class="rounded border p-2">
            @else
                <p class="text-muted">Carga tu QR para mostrarlo al cliente.</p>
            @endif
        </div>
        <div class="col-md-8 form-group">
            <label for="yape_qr">QR de Yape</label>
            <input id="yape_qr" name="yape_qr" type="file" accept="image/jpeg,image/png,image/webp" class="form-control-file">
            <small class="form-text text-muted">JPG, PNG o WebP, hasta 5 MB. Si no eliges una imagen, se conserva el QR actual. El número es opcional cuando hay un QR.</small>
        </div>
    </div>
    <div class="form-group"><label for="whatsapp">WhatsApp para coordinar pedidos</label><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', config('tienda.whatsapp')) }}" inputmode="numeric" pattern="9[0-9]{8}" maxlength="9" class="form-control"><small class="form-text text-muted">Número peruano de 9 dígitos, sin +51. Puede ser diferente del número de Yape.</small></div>
    <button class="btn btn-primary">Guardar datos</button>
</div></form>
<div class="card"><div class="card-body"><h2 class="h5">Operación de la tienda</h2>
    <p>Los compradores coordinan dirección, cobertura, tarifa y plazo contigo por WhatsApp. Registra la dirección acordada y el costo en el detalle administrativo del pedido antes de recibir el pago.</p>
    <p>Las constancias de Yape, Plin y transferencia requieren verificación manual en la cuenta del negocio. Los comprobantes de venta deben emitirse por el procedimiento del negocio y adjuntarse al pedido.</p>
</div></div>
@stop
