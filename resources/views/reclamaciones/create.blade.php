@extends('layouts.app')
@section('title', 'Libro de Reclamaciones | ByteStore')
@section('contenido')
<div class="mx-auto max-w-3xl">
<h1 class="mb-3 text-3xl font-bold">Libro de Reclamaciones</h1>
<p class="mb-5 text-slate-600">Registra una queja o reclamo sin necesidad de iniciar sesión. Al enviar recibirás una constancia que puedes imprimir y conservar.</p>
<p class="mb-5 text-sm text-slate-600">Reclamo: disconformidad con el producto o servicio. Queja: disconformidad con la atención. El plazo de respuesta es de 15 días hábiles. La presentación no impide acudir a Indecopi.</p>
<div class="mb-5 rounded-xl bg-white p-4"><strong>ByteStore</strong>
    @if(config('tienda.razon_social'))<p>{{ config('tienda.razon_social') }}</p>@endif
    @if(config('tienda.ruc'))<p>RUC: {{ config('tienda.ruc') }}</p>@endif
    @if(config('tienda.retiro.direccion'))<p>{{ config('tienda.retiro.direccion') }}</p>@endif
</div>
@include('partials.form-errors')
<form action="{{ route('reclamaciones.store') }}" method="POST" class="space-y-5 rounded-2xl bg-white p-5">
    @csrf
    <h2 class="text-lg font-bold">Datos del consumidor</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach(['nombre' => 'Nombre completo', 'documento' => 'DNI / documento', 'email' => 'Correo para respuesta', 'telefono' => 'Teléfono', 'direccion' => 'Dirección'] as $campo => $texto)
            <label for="{{ $campo }}" class="block text-sm">{{ $texto }}<input id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" value="{{ old($campo) }}" required maxlength="{{ $campo === 'documento' || $campo === 'telefono' ? 20 : ($campo === 'nombre' ? 150 : 255) }}" class="mt-1 w-full rounded-lg border border-slate-300 p-3"></label>
        @endforeach
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="menor" value="1" @checked(old('menor'))> El consumidor es menor de edad</label>
    <label for="representante" class="block text-sm">Nombre y datos del padre, madre o representante (si es menor)<input id="representante" name="representante" maxlength="255" value="{{ old('representante') }}" class="mt-1 w-full rounded-lg border border-slate-300 p-3"></label>
    <div class="grid gap-4 sm:grid-cols-3">
        <label for="tipo" class="block text-sm">Tipo<select id="tipo" name="tipo" class="mt-1 w-full rounded-lg border border-slate-300 p-3">@foreach(['Reclamo', 'Queja'] as $valor)<option @selected(old('tipo') === $valor)>{{ $valor }}</option>@endforeach</select></label>
        <label for="bien_tipo" class="block text-sm">Bien contratado<select id="bien_tipo" name="bien_tipo" class="mt-1 w-full rounded-lg border border-slate-300 p-3">@foreach(['Producto', 'Servicio'] as $valor)<option @selected(old('bien_tipo') === $valor)>{{ $valor }}</option>@endforeach</select></label>
        <label for="monto" class="block text-sm">Monto (S/, si corresponde)<input id="monto" name="monto" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('monto') }}" class="mt-1 w-full rounded-lg border border-slate-300 p-3"></label>
    </div>
    @foreach(['bien_descripcion' => 'Describe el producto o servicio y la compra', 'detalle' => 'Detalle de la queja o reclamo', 'solicitud' => '¿Qué solución solicitas?'] as $campo => $texto)
        <label for="{{ $campo }}" class="block text-sm">{{ $texto }}<textarea id="{{ $campo }}" name="{{ $campo }}" required maxlength="{{ $campo === 'detalle' ? 10000 : ($campo === 'solicitud' ? 5000 : 3000) }}" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 p-3">{{ old($campo) }}</textarea></label>
    @endforeach
    <p class="text-sm text-slate-600">Usaremos estos datos para atender y conservar tu reclamación. Conserva el enlace privado de la constancia.</p>
    <button class="min-h-12 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white">Registrar hoja</button>
</form>
</div>
@endsection

