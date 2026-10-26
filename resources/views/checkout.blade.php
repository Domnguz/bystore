@extends('layouts.app')
@section('title', 'Preparar pedido | ByteStore')
@section('body-class', 'bs-payment-page')
@section('contenido')
<a href="{{ route('carrito') }}" class="text-sm text-blue-700 underline">← Volver al carrito</a>
<h1 class="mb-3 mt-4 text-3xl font-bold">Prepara tu pedido</h1>
<p class="mb-6 text-slate-600">Registra tu pedido y elige el método de pago disponible desde Mis pedidos. Si eliges envío, primero confirmaremos contigo el costo y el plazo por WhatsApp.</p>
@include('partials.form-errors')
<form action="{{ route('checkout') }}" method="POST" id="formConfirmarPedido" class="grid items-start gap-6 lg:grid-cols-3">
    @csrf
    <input type="hidden" name="checkout_token" value="{{ $token }}">
    <div class="space-y-6 lg:col-span-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-lg font-bold">Datos del destinatario</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label for="destinatario" class="block text-sm">Nombre completo
                    <input id="destinatario" name="destinatario" required maxlength="150" autocomplete="name" value="{{ old('destinatario', trim(auth()->user()->name.' '.auth()->user()->apellidos)) }}" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                </label>
                <label for="telefono" class="block text-sm">Teléfono
                    <input id="telefono" name="telefono" required maxlength="20" autocomplete="tel" value="{{ old('telefono', auth()->user()->telefono) }}" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                </label>
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-lg font-bold">¿Cómo recibirás tu compra?</h2>
            <fieldset>
                <legend class="mb-3 block text-sm font-semibold">Elige cómo recibir tu compra</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @if(config('tienda.envio.habilitado'))
                        <label class="group relative block cursor-pointer">
                            <input class="peer sr-only" type="radio" name="metodo_entrega" value="envio" required data-metodo-entrega @checked(old('metodo_entrega', config('tienda.retiro.habilitado') ? 'retiro' : 'envio') === 'envio')>
                            <span class="flex h-full min-h-32 gap-3 rounded-xl border border-slate-200 bg-white p-4 transition duration-200 group-hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-100 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-200">
                                <svg class="mt-1 h-8 w-8 shrink-0 text-slate-400 transition group-has-[:checked]:text-blue-700" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                                    <circle cx="8" cy="23" r="4" stroke="currentColor" stroke-width="2"/><circle cx="24" cy="23" r="4" stroke="currentColor" stroke-width="2"/><path d="M8 23h7l-4-8h6l4 8m-5-8 2-4h5m-2 12h3l3-7h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 11h7l2 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-slate-900">Envío a domicilio</span>
                                    <span class="mt-1 block text-sm leading-5 text-slate-600">Coordinamos contigo el costo y la entrega por WhatsApp.</span>
                                </span>
                                <svg class="mt-1 hidden h-5 w-5 shrink-0 text-blue-700 group-has-[:checked]:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06l-3.47 3.47-1.72-1.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd"/></svg>
                            </span>
                        </label>
                    @endif
                    @if(config('tienda.retiro.habilitado'))
                        <label class="group relative block cursor-pointer">
                            <input class="peer sr-only" type="radio" name="metodo_entrega" value="retiro" required data-metodo-entrega @checked(old('metodo_entrega', config('tienda.retiro.habilitado') ? 'retiro' : 'envio') === 'retiro')>
                            <span class="flex h-full min-h-32 gap-3 rounded-xl border border-slate-200 bg-white p-4 transition duration-200 group-hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-100 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-200">
                                <svg class="mt-1 h-8 w-8 shrink-0 text-slate-400 transition group-has-[:checked]:text-blue-700" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                                    <path d="M5 13h22v14H5zM4 13l3-8h18l3 8" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M4 13c0 2 3 2 3 0 0 2 4 2 4 0 0 2 4 2 4 0 0 2 4 2 4 0 0 2 3 2 3 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M11 27v-8h7v8m4-9h2v4h-2" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-semibold text-slate-900">Recojo en tienda</span>
                                    <span class="mt-1 block text-sm leading-5 text-slate-600">Sin costo de envío. Coordinamos contigo la dirección y el horario.</span>
                                </span>
                                <svg class="mt-1 hidden h-5 w-5 shrink-0 text-blue-700 group-has-[:checked]:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06l-3.47 3.47-1.72-1.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd"/></svg>
                            </span>
                        </label>
                    @endif
                </div>
            </fieldset>
            <div id="detalleRetiro" class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                {{ config('tienda.retiro.direccion') ?: 'Coordinaremos contigo la dirección y el horario para recoger tu pedido.' }}
            </div>
            <p id="detalleEnvio" class="mt-3 text-sm text-slate-600" hidden>Deja tu dirección ahora o compártela con la tienda por WhatsApp.</p>
            <div id="datosEnvio" class="mt-4 grid gap-4 sm:grid-cols-3">
                @foreach(['departamento' => 'Departamento', 'provincia' => 'Provincia', 'distrito' => 'Distrito'] as $campo => $etiqueta)
                    <label for="{{ $campo }}" class="block text-sm">{{ $etiqueta }} (opcional)
                        <input id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo) }}" maxlength="100" data-envio class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                    </label>
                @endforeach
                <label for="direccion" class="block text-sm sm:col-span-3">Dirección de entrega (opcional)
                    <input id="direccion" name="direccion" value="{{ old('direccion') }}" maxlength="255" autocomplete="street-address" data-envio class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                </label>
                <label for="referencia" class="block text-sm sm:col-span-3">Referencia (opcional)
                    <input id="referencia" name="referencia" value="{{ old('referencia') }}" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                </label>
            </div>
        </section>
    </div>
    <aside class="rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-28">
        <h2 class="text-lg font-bold">Resumen</h2>
        @foreach($carrito as $item)
            <div class="border-b border-slate-100 py-4"><p class="text-sm font-medium">{{ $item['nombre'] }}</p><p class="mt-1 text-sm text-slate-600">{{ $item['cantidad'] }} × S/ {{ number_format($item['precio'], 2) }}</p></div>
        @endforeach
        <div class="mt-5 flex justify-between gap-3 font-bold"><span>Productos</span><span>S/ {{ number_format(collect($carrito)->sum(fn($item) => $item['precio'] * $item['cantidad']), 2) }}</span></div>
        <div class="mt-2 space-y-1 border-t border-slate-100 pt-3 text-sm text-slate-600">
            <div class="flex justify-between gap-3"><span>Base imponible</span><span>S/ {{ number_format($igv['base_imponible'], 2) }}</span></div>
            <div class="flex justify-between gap-3"><span>IGV incluido ({{ number_format((float) $igv['tasa'], 0) }}%)</span><span>S/ {{ number_format($igv['igv'], 2) }}</span></div>
            <p class="text-xs">El IGV ya está incluido en los precios; no se suma otra vez.</p>
        </div>
        <p id="resumenEnvio" class="mt-3 text-sm text-slate-600"></p>
        <p class="mt-3 text-sm text-slate-600">Las unidades se reservan durante {{ config('tienda.reserva_horas') }} horas. Registrar el pedido no confirma un pago.</p>
        <button id="botonConfirmarPedido" class="mt-6 min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Continuar al pago</button>
        <p class="mt-3 text-center text-xs text-slate-500">Tu pedido se registrará en la tienda. El pago se confirma después de revisar tu constancia.</p>
        <p id="estadoConfirmarPedido" role="status" class="mt-3 text-sm text-slate-500"></p>
    </aside>
</form>
<script>
(() => {
    const metodos = document.querySelectorAll('[data-metodo-entrega]');
    if (!metodos.length) return;
    const datosEnvio = document.getElementById('datosEnvio');
    const detalleRetiro = document.getElementById('detalleRetiro');
    const detalleEnvio = document.getElementById('detalleEnvio');
    const resumenEnvio = document.getElementById('resumenEnvio');
    const actualizar = () => {
        const seleccionado = document.querySelector('[data-metodo-entrega]:checked')?.value;
        const envio = seleccionado === 'envio';
        datosEnvio.hidden = !envio;
        detalleRetiro.hidden = envio;
        detalleEnvio.hidden = !envio;
        resumenEnvio.textContent = envio
            ? 'Envío por coordinar en WhatsApp. Confirmaremos el total antes del pago.'
            : 'Recojo en tienda: sin costo de envío (S/ 0.00).';
    };
    metodos.forEach(metodo => metodo.addEventListener('change', actualizar));
    actualizar();
    const form = document.getElementById('formConfirmarPedido');
    const boton = document.getElementById('botonConfirmarPedido');
    form.addEventListener('submit', event => {
        if (boton.disabled) { event.preventDefault(); return; }
        boton.disabled = true;
        document.getElementById('estadoConfirmarPedido').textContent = 'Registrando tu pedido…';
    });
    window.addEventListener('pageshow', () => { boton.disabled = false; });
})();
</script>
@endsection
