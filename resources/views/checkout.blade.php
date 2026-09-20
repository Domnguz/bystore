@extends('layouts.app')

@section('contenido')

{{-- Calculamos el total del pedido y la cantidad de unidades. --}}
@php
    $cantidadTotal = collect($carrito)->sum('cantidad');

    $total = collect($carrito)->sum(function ($item) {
        return $item['precio'] * $item['cantidad'];
    });
@endphp

<div class="mx-auto w-full max-w-6xl">

    {{-- ENCABEZADO: enlace para volver y revisar el carrito. --}}
    <header class="mb-6 sm:mb-8">
        <a
            href="{{ url('/carrito') }}"
            class="inline-flex min-h-11 items-center gap-2 text-sm
                   font-medium text-gray-500 transition hover:text-blue-600"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path
                    d="m12 19-7-7 7-7M5 12h14"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            Volver al carrito
        </a>

        <p class="mt-4 text-xs font-semibold uppercase tracking-widest text-blue-600">
            ByteStore / Tu pedido
        </p>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            Revisa y confirma
        </h1>

        <p class="mt-2 text-sm leading-6 text-gray-500">
            Comprueba los productos y las cantidades antes de confirmar.
        </p>
    </header>

    {{-- ERRORES: mensajes enviados por el controlador. --}}
    @if (session('error'))
        <div
            role="alert"
            class="mb-5 rounded-xl border border-red-200
                   bg-red-50 p-4 text-sm text-red-700"
        >
            {{ session('error') }}
        </div>
    @endif

    {{-- ERRORES DE VALIDACIÓN: por ejemplo, datos que no sean válidos. --}}
    @if ($errors->any())
        <div
            role="alert"
            class="mb-5 rounded-xl border border-red-200
                   bg-red-50 p-4 text-sm text-red-700"
        >
            <p class="font-semibold">
                Revisa lo siguiente:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($carrito) > 0)

        {{-- DISTRIBUCIÓN:
             Celular: una columna.
             PC: productos a la izquierda y resumen a la derecha. --}}
        <div class="grid items-start gap-5 lg:grid-cols-3 lg:gap-8">

            <div class="min-w-0 lg:col-span-2">

                {{-- PRODUCTOS QUE SE INCLUIRÁN EN EL PEDIDO. --}}
                <section
                    aria-labelledby="titulo-productos"
                    class="overflow-hidden rounded-2xl border
                           border-gray-200 bg-white"
                >
                    <div class="flex flex-wrap items-center justify-between
                                gap-3 border-b border-gray-100 p-4 sm:p-5">
                        <h2
                            id="titulo-productos"
                            class="text-base font-bold text-gray-900 sm:text-lg"
                        >
                            Tus productos
                        </h2>

                        <span class="rounded-full bg-blue-50 px-3 py-1.5
                                     text-xs font-medium text-blue-700">
                            {{ $cantidadTotal }}
                            {{ $cantidadTotal == 1 ? 'unidad' : 'unidades' }}
                        </span>
                    </div>

                    <div class="divide-y divide-gray-100">

                        @foreach ($carrito as $item)

                            @php
                                $subtotal = $item['precio'] * $item['cantidad'];
                            @endphp

                            <article class="p-4 sm:p-5">
                                <div class="flex items-start gap-3 sm:gap-5">

                                    {{-- FOTO: tamaño uniforme sin recortar. --}}
                                    <div class="flex h-20 w-20 shrink-0
                                                items-center justify-center
                                                overflow-hidden rounded-xl
                                                border border-gray-100
                                                bg-white sm:h-24 sm:w-24">
                                        @if (!empty($item['imagen']))
                                            <img
                                                src="{{ asset('storage/' . $item['imagen']) }}"
                                                alt="{{ $item['nombre'] }}"
                                                loading="lazy"
                                                decoding="async"
                                                class="h-full w-full object-contain p-2"
                                            >
                                        @else
                                            <span class="px-2 text-center
                                                         text-xs text-gray-400">
                                                Sin imagen
                                            </span>
                                        @endif
                                    </div>

                                    {{-- NOMBRE Y PRECIO UNITARIO. --}}
                                    <div class="min-w-0 flex-1">
                                        <h3 class="break-words text-sm font-semibold
                                                   leading-5 text-gray-900
                                                   sm:text-base sm:leading-6">
                                            {{ $item['nombre'] }}
                                        </h3>

                                        <p class="mt-2 text-xs leading-5
                                                  text-gray-500 sm:text-sm">
                                            S/ {{ number_format($item['precio'], 2) }}
                                            por unidad
                                        </p>

                                        {{-- Se ajusta a otra línea si falta espacio. --}}
                                        <div class="mt-3 flex flex-wrap items-center
                                                    justify-between gap-3">
                                            <span class="rounded-lg bg-gray-100
                                                         px-2.5 py-1.5 text-xs
                                                         font-medium text-gray-600">
                                                Cantidad: {{ $item['cantidad'] }}
                                            </span>

                                            <p class="text-sm font-bold
                                                      text-gray-900 sm:text-base">
                                                <span class="sr-only">
                                                    Subtotal:
                                                </span>

                                                S/ {{ number_format($subtotal, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>

                        @endforeach

                    </div>
                </section>

                {{-- Permite corregir el pedido antes de enviarlo. --}}
                <p class="mt-4 text-sm leading-6 text-gray-500">
                    ¿Quieres quitar algún producto?

                    <a
                        href="{{ url('/carrito') }}"
                        class="font-medium text-blue-600 underline
                               underline-offset-4 hover:text-blue-700"
                    >
                        Editar mi carrito
                    </a>
                </p>
            </div>

            {{-- RESUMEN: permanece visible al desplazarse en PC. --}}
            <aside
                aria-labelledby="titulo-confirmacion"
                class="min-w-0 rounded-2xl border border-gray-200
                       bg-white p-5 sm:p-6 lg:sticky lg:top-24"
            >
                <h2
                    id="titulo-confirmacion"
                    class="text-lg font-bold text-gray-900"
                >
                    Resumen del pedido
                </h2>

                <dl class="mt-5 space-y-4">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-gray-500">
                            Unidades
                        </dt>

                        <dd class="font-medium text-gray-900">
                            {{ $cantidadTotal }}
                        </dd>
                    </div>

                    <div class="flex flex-wrap items-center justify-between
                                gap-3 border-t border-gray-100 pt-4">
                        <dt class="font-semibold text-gray-900">
                            Total
                        </dt>

                        <dd class="text-2xl font-bold tracking-tight text-blue-600">
                            S/ {{ number_format($total, 2) }}
                        </dd>
                    </div>
                </dl>

                {{-- FORMULARIO:
                     Conserva el POST original.
                     @csrf protege el envío del formulario.
                     El controlador procesa y registra el pedido. --}}
                <form
                    id="formConfirmarPedido"
                    action="{{ url('/checkout') }}"
                    method="POST"
                    class="mt-6"
                >
                    @csrf

                    <button
                        id="botonConfirmarPedido"
                        type="submit"
                        class="flex min-h-12 w-full items-center justify-center
                               gap-2 rounded-xl bg-blue-600 px-4 py-3
                               text-sm font-semibold text-white transition
                               hover:bg-blue-700 disabled:cursor-wait
                               disabled:opacity-60"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                d="m5 12 4 4L19 6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <span id="textoConfirmarPedido">
                            Confirmar pedido
                        </span>
                    </button>

                    <p
                        id="estadoConfirmarPedido"
                        role="status"
                        class="mt-3 text-center text-xs leading-5 text-gray-500"
                    ></p>
                </form>

                <a
                    href="{{ url('/carrito') }}"
                    class="mt-1 flex min-h-11 items-center justify-center
                           text-sm font-medium text-gray-500
                           transition hover:text-blue-600"
                >
                    Volver al carrito
                </a>
            </aside>
        </div>

    @else

        {{-- ESTADO VACÍO: no muestra el botón para confirmar sin productos. --}}
        <section class="rounded-2xl border border-dashed border-gray-300
                        bg-white px-5 py-12 text-center sm:py-16">
            <div class="mx-auto flex h-16 w-16 items-center justify-center
                        rounded-full bg-blue-50 text-blue-600">
                <svg
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    aria-hidden="true"
                >
                    <path
                        d="M3 3h2l2.4 12h11.2L21 7H6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <circle cx="9" cy="20" r="1" />
                    <circle cx="18" cy="20" r="1" />
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-bold text-gray-900 sm:text-2xl">
                Todavía no tienes productos
            </h2>

            <p class="mt-2 text-sm leading-6 text-gray-500">
                Agrega productos al carrito para preparar tu pedido.
            </p>

            <a
                href="{{ url('/productos') }}"
                class="mt-6 inline-flex min-h-12 items-center justify-center
                       rounded-xl bg-blue-600 px-6 py-3 text-sm
                       font-semibold text-white transition hover:bg-blue-700"
            >
                Explorar productos
            </a>
        </section>

    @endif
</div>

{{-- Evita pulsaciones repetidas mientras se envía el formulario.
     El envío continúa normalmente hacia el controlador. --}}
<script>
(() => {
    const formulario = document.getElementById('formConfirmarPedido');
    const boton = document.getElementById('botonConfirmarPedido');
    const texto = document.getElementById('textoConfirmarPedido');
    const estado = document.getElementById('estadoConfirmarPedido');

    if (!formulario || !boton || !texto || !estado) return;

    let enviando = false;

    formulario.addEventListener('submit', (event) => {
        if (enviando) {
            event.preventDefault();
            return;
        }

        enviando = true;
        boton.disabled = true;
        texto.textContent = 'Confirmando…';
        estado.textContent = 'Estamos enviando tu pedido.';
        formulario.setAttribute('aria-busy', 'true');
    });

    // Restablece el botón si vuelves con la flecha del navegador.
    window.addEventListener('pageshow', () => {
        enviando = false;
        boton.disabled = false;
        texto.textContent = 'Confirmar pedido';
        estado.textContent = '';
        formulario.removeAttribute('aria-busy');
    });
})();
</script>

@endsection