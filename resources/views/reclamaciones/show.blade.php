@extends('layouts.app')
@section('title', 'Hoja de reclamación #'.$reclamacion->id.' | ByteStore')
@section('contenido')
<article class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
    <h1 class="text-2xl font-bold">Hoja de reclamación #{{ str_pad($reclamacion->id, 6, '0', STR_PAD_LEFT) }}</h1>
    <p class="mt-2 text-sm">{{ $reclamacion->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</p>
    <p class="mt-3">Proveedor: {{ $reclamacion->proveedor['razon_social'] ?: $reclamacion->proveedor['nombre'] }} · RUC: {{ $reclamacion->proveedor['ruc'] ?: 'Por completar por el negocio' }} · {{ $reclamacion->proveedor['direccion'] }}</p>
    <h2 class="mb-2 mt-6 font-bold">Consumidor</h2>
    <p>{{ $reclamacion->nombre }} · {{ $reclamacion->documento }}</p><p>{{ $reclamacion->email }} · {{ $reclamacion->telefono }}</p><p>{{ $reclamacion->direccion }}</p>
    @if($reclamacion->menor)<p>Representante: {{ $reclamacion->representante }}</p>@endif
    <h2 class="mb-2 mt-6 font-bold">{{ $reclamacion->tipo }} · {{ $reclamacion->bien_tipo }}</h2>
    <p class="whitespace-pre-line">{{ $reclamacion->bien_descripcion }}</p>
    @if($reclamacion->monto !== null)<p>Monto: S/ {{ number_format($reclamacion->monto, 2) }}</p>@endif
    <h2 class="mb-2 mt-6 font-bold">Detalle</h2><p class="whitespace-pre-line">{{ $reclamacion->detalle }}</p>
    <h2 class="mb-2 mt-6 font-bold">Solicitud</h2><p class="whitespace-pre-line">{{ $reclamacion->solicitud }}</p>
    <h2 class="mb-2 mt-6 font-bold">Respuesta del proveedor</h2>
    @if($reclamacion->respondido_at)<p class="whitespace-pre-line">{{ $reclamacion->respuesta }}</p><p class="mt-2 text-sm">Registrada el {{ $reclamacion->respondido_at->timezone('America/Lima')->format('d/m/Y H:i') }}. Medio: {{ $reclamacion->medio_respuesta }}.</p>@else<p>Pendiente de atención.</p>@endif
    <p class="mt-6 text-sm text-slate-600">El plazo de respuesta es de 15 días hábiles. Esta hoja no impide acudir a Indecopi. Conserva esta constancia y no compartas su enlace privado.</p>
    <button type="button" onclick="window.print()" class="mt-5 min-h-11 rounded-lg bg-blue-600 px-5 py-3 text-white print:hidden">Imprimir / guardar como PDF</button>
</article>
@endsection

