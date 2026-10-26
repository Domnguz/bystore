@extends('layouts.app')
@section('title', 'Entrega y contacto | ByteStore')
@section('contenido')
<div class="bs-contact-page mx-auto max-w-5xl">
    <h1 class="mb-10 text-3xl font-bold tracking-tight sm:text-4xl">Entrega y contacto</h1>
    <section id="retiro-tienda" class="bs-contact-section"><h2 class="text-xl font-bold">Retiro en tienda física</h2>
        <div class="bs-contact-section-body">
        <p class="mt-3">Puedes seleccionar retiro al preparar tu pedido. Esta modalidad no tiene costo de envío.</p>
        <p class="mt-3">{{ config('tienda.retiro.direccion') ?: 'La dirección y el horario se confirmarán con la tienda antes del pago y retiro.' }}</p>
        </div>
    </section>
    <section id="entregas" class="bs-contact-section"><h2 class="text-xl font-bold">Envío a domicilio</h2>
        <div class="bs-contact-section-body">
        <p class="mt-3">Coordinamos los envíos por WhatsApp. Escríbenos para acordar la dirección, cobertura, tarifa y plazo antes de realizar el pago.</p>
        @if($whatsapp = \App\Services\WhatsApp::enlace('Hola, quiero coordinar el envío de una compra en ByteStore.'))
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex min-h-12 items-center rounded-lg bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">Consultar envío por WhatsApp</a>
        @endif
        <p class="mt-3">Consulta el total confirmado y el avance de tu entrega en <a href="{{ route('mis-pedidos.index') }}" class="text-blue-700 underline">Mis pedidos</a>.</p>
        </div>
    </section>
    <section id="metodos-pago" class="bs-contact-section"><h2 class="text-xl font-bold">Métodos de pago</h2>
        <div class="bs-contact-section-body">
        <p class="mt-3">Después de registrar el pedido verás los métodos habilitados por la tienda (Yape, Plin o transferencia bancaria) cuando el total esté confirmado. Sigue las instrucciones del método elegido y envía la constancia desde tu pedido.</p>
        <p class="mt-3">La tienda verificará el ingreso real. Una captura enviada mantiene el pago pendiente de revisión.</p>
        </div>
    </section>
    <section class="bs-contact-section"><h2 class="text-xl font-bold">Datos del negocio</h2>
        <div class="bs-contact-section-body">
        <p class="mt-3">ByteStore</p>
        @if(config('tienda.razon_social'))<p>{{ config('tienda.razon_social') }}</p>@endif
        @if(config('tienda.ruc'))<p>RUC: {{ config('tienda.ruc') }}</p>@endif
        @if(config('tienda.contacto'))<p class="mt-3">Contacto: {{ config('tienda.contacto') }}</p>@endif
        <a href="{{ route('reclamaciones.create') }}" class="mt-4 inline-block text-blue-700 underline">Libro de Reclamaciones</a>
        </div>
    </section>
</div>
@endsection
