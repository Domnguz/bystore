@extends('layouts.app')
@section('title', 'Pedido #'.$pedido->id.' | ByteStore')
@section('body-class', 'bs-payment-page')
@section('contenido')
<a href="{{ route('mis-pedidos.index') }}" class="text-sm text-blue-700 underline">← Mis pedidos</a>
<div class="mb-6 mt-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-3xl font-bold">Pedido #{{ $pedido->id }}</h1>
    <span class="rounded-full bg-blue-50 px-4 py-2 font-semibold text-blue-800">{{ $pedido->estado_visible }}</span>
</div>
@include('partials.form-errors')
@include('partials.pedido-sync', ['syncUrl' => route('mis-pedidos.estado', $pedido)])
<ol class="mb-6 grid grid-cols-3 gap-2 text-center text-xs sm:gap-4 sm:text-sm" aria-label="Etapas del pago">
    @foreach(['Pedido registrado', 'Pago en revisión', 'Pago confirmado'] as $paso)
        @php $completo = $pedido->estado !== 'Rechazado' && ($loop->first || ($loop->iteration === 2 && $pedido->comprobante_pago) || in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado'])); @endphp
        <li class="rounded-xl border px-2 py-3 {{ $completo ? 'border-purple-200 bg-purple-50 text-purple-800' : 'border-slate-200 bg-white text-slate-400' }}"><span class="mb-1 block font-bold">{{ $loop->iteration }}</span>{{ $paso }}</li>
    @endforeach
</ol>
<div class="grid min-w-0 items-start gap-6 lg:grid-cols-2">
    <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 text-xl font-bold">Tu compra</h2>
        @foreach($pedido->detalles as $detalle)
            @php $resenaPropia = $detalle->producto?->resenas->first(); @endphp
            <div class="flex justify-between gap-4 border-b border-slate-100 py-3">
                <div class="min-w-0"><p class="break-words font-medium">{{ $detalle->nombre_producto ?? $detalle->producto?->nombre ?? 'Producto' }}</p><p class="text-sm text-slate-500">{{ $detalle->cantidad }} × S/ {{ number_format($detalle->precio, 2) }}</p></div>
                <p class="shrink-0">S/ {{ number_format($detalle->subtotal ?? $detalle->cantidad * $detalle->precio, 2) }}</p>
            </div>
            @if($pedido->estado === 'Entregado' && $detalle->producto)
                <form action="{{ route('mis-pedidos.productos.calificar', [$pedido, $detalle->producto]) }}" method="POST" class="mb-4 rounded-xl bg-slate-50 p-4">
                    @csrf
                    <fieldset>
                        <legend class="font-semibold">{{ $resenaPropia ? 'Tu calificación' : '¿Qué te pareció este producto?' }}</legend>
                        <div class="mt-2 flex items-center gap-1" role="radiogroup" aria-label="Calificación de 1 a 5 estrellas">
                            @for($estrella = 1; $estrella <= 5; $estrella++)
                                <label class="cursor-pointer text-3xl leading-none text-slate-300 transition-colors hover:text-amber-400" data-estrella-label>
                                    <input class="sr-only" type="radio" name="calificacion" value="{{ $estrella }}" required @checked((int) old('calificacion', $resenaPropia?->calificacion) === $estrella)>
                                    <span aria-hidden="true">★</span>
                                    <span class="sr-only">{{ $estrella }} {{ $estrella === 1 ? 'estrella' : 'estrellas' }}</span>
                                </label>
                            @endfor
                        </div>
                    </fieldset>
                    <label class="mt-3 block text-sm font-medium" for="comentario-{{ $detalle->producto->id }}">Comentario (opcional)</label>
                    <textarea id="comentario-{{ $detalle->producto->id }}" name="comentario" rows="2" maxlength="1000" class="mt-1 w-full rounded-lg border border-slate-300 p-3" placeholder="Cuéntales a otros compradores tu experiencia">{{ old('comentario', $resenaPropia?->comentario) }}</textarea>
                    <button type="submit" class="mt-3 min-h-10 rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">{{ $resenaPropia ? 'Actualizar calificación' : 'Enviar calificación' }}</button>
                </form>
            @endif
        @endforeach
        <dl class="mt-5 space-y-2">
            <div class="flex justify-between"><dt>Productos</dt><dd>S/ {{ number_format($pedido->subtotal_productos ?? $pedido->total, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Entrega</dt><dd>{{ $pedido->entrega_cotizada ? 'S/ '.number_format($pedido->costo_envio, 2) : 'Por cotizar' }}</dd></div>
            <div class="flex justify-between border-t pt-3 text-lg font-bold"><dt>{{ $pedido->entrega_cotizada ? 'Total a pagar' : 'Subtotal de productos' }}</dt><dd>S/ {{ number_format($pedido->total, 2) }}</dd></div>
        </dl>
        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm">
            <div class="flex justify-between"><span>Base imponible</span><span>S/ {{ number_format($igv['base_imponible'], 2) }}</span></div>
            <div class="mt-1 flex justify-between"><span>IGV incluido ({{ number_format((float) $igv['tasa'], 0) }}%)</span><span>S/ {{ number_format($igv['igv'], 2) }}</span></div>
        </div>
        <h2 class="mb-2 mt-7 text-lg font-bold">Entrega</h2>
        <p>{{ $pedido->destinatario }} · {{ $pedido->telefono }}</p>
        @if($pedido->metodo_entrega === 'retiro')
            <p class="mt-2">Retiro en tienda.</p>
            <p class="text-sm text-slate-600">{{ config('tienda.retiro.direccion') ?: 'Coordina la dirección y el horario de retiro con la tienda.' }}</p>
        @else
            @if($pedido->direccion)
                <p class="mt-2">{{ $pedido->direccion }}</p><p>{{ implode(', ', array_filter([$pedido->distrito, $pedido->provincia, $pedido->departamento])) }}</p>
            @else
                <p class="mt-2 text-slate-600">La dirección de envío se coordinará con la tienda por WhatsApp.</p>
            @endif
            @if($pedido->referencia)<p class="text-sm text-slate-600">Referencia: {{ $pedido->referencia }}</p>@endif
        @endif
        @if($pedido->seguimiento)<p class="mt-3 font-medium">Seguimiento: {{ $pedido->transportista }} · {{ $pedido->seguimiento }}</p>@endif
        @if($pedido->tipo_comprobante)
            <p class="mt-4 text-sm text-slate-500">Comprobante solicitado: {{ $pedido->tipo_comprobante }}. La tienda emitirá el comprobante de venta correspondiente.</p>
        @endif
        @if(!$pedido->comprobante_venta && $pedido->entrega_cotizada && in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado'], true))
            <a href="{{ route('mis-pedidos.boleta-pdf', $pedido) }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Descargar resumen informativo de compra</a>
            <p class="mt-2 text-xs text-slate-500">PDF informativo. No reemplaza el comprobante electrÃ³nico oficial.</p>
        @endif
        @if($pedido->comprobante_venta)<a href="{{ route('mis-pedidos.comprobante-venta', $pedido) }}" class="mt-3 inline-block text-green-700 underline">Descargar resumen informativo de compra</a>@endif
    </section>
    <div class="min-w-0 space-y-5">
        @include('pedidos._pago')
        @if($whatsapp = \App\Services\WhatsApp::pedido($pedido))
            <section class="rounded-2xl border border-green-200 bg-white p-5">
                <h2 class="text-xl font-bold">{{ $pedido->metodo_entrega === 'retiro' ? 'Coordina tu retiro' : 'Coordina tu envío' }}</h2>
                <p class="mt-3 text-slate-600">Conversemos por WhatsApp para acordar {{ $pedido->metodo_entrega === 'retiro' ? 'la dirección y el horario de retiro' : 'la dirección, el costo y el plazo de entrega' }}.</p>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex min-h-12 items-center rounded-lg bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">{{ $pedido->metodo_entrega === 'retiro' ? 'Coordinar retiro por WhatsApp' : 'Coordinar envío por WhatsApp' }}</a>
                <p class="mt-3 text-sm text-slate-500">Se abrirá un mensaje con tu número de pedido y productos. Revísalo y envíalo para iniciar la conversación.</p>
            </section>
        @endif
        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold">Historial del pedido</h2>
            <ol class="mt-4 space-y-4">
                @foreach($pedido->eventos as $evento)
                    <li class="border-l-2 border-blue-200 pl-4"><p class="font-medium">{{ $evento->estado }}</p><p class="text-sm text-slate-600">{{ $evento->nota }}</p><time class="text-xs text-slate-500">{{ $evento->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</time></li>
                @endforeach
            </ol>
        </section>
    </div>
</div>
<p class="mt-5 text-center text-xs text-slate-500">El estado del pedido se actualiza automáticamente. Aquí verás la confirmación del administrador.</p>
<script>
    document.querySelectorAll('[role="radiogroup"]').forEach((grupo) => {
        const actualizar = () => {
            const valor = Number(grupo.querySelector('input:checked')?.value || 0);
            grupo.querySelectorAll('[data-estrella-label]').forEach((etiqueta, indice) => {
                etiqueta.classList.toggle('text-amber-400', indice < valor);
                etiqueta.classList.toggle('text-slate-300', indice >= valor);
            });
        };
        grupo.addEventListener('change', actualizar);
        actualizar();
    });
</script>
@endsection
