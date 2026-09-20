@php
    // Obtiene los productos guardados en la sesión.
    $carrito = session('carrito', []);

    // Cuenta unidades y calcula el total.
    $cantidadTotal = collect($carrito)->sum('cantidad');

    $total = collect($carrito)->sum(function ($item) {
        return (float) $item['precio'] * (int) $item['cantidad'];
    });
@endphp

@if (count($carrito) > 0)

    {{-- RESUMEN DE UNIDADES --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-slate-900">
            Tus productos
        </p>

        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
            {{ $cantidadTotal }}
            {{ $cantidadTotal == 1 ? 'unidad' : 'unidades' }}
        </span>
    </div>


    {{-- LISTA DE PRODUCTOS --}}
    <div class="space-y-3">

        @foreach ($carrito as $item)

            @php
                // Precio de este producto multiplicado por su cantidad.
                $subtotal = (float) $item['precio'] * (int) $item['cantidad'];
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">

                <div class="flex items-start gap-3">

                    {{-- FOTO DEL PRODUCTO --}}
                    <a
                        href="{{ route('tienda.productos.show', $item['id']) }}"
                        aria-label="Ver {{ $item['nombre'] }}"
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-white sm:h-20 sm:w-20"
                    >
                        @if (!empty($item['imagen']))

                            <img
                                src="{{ asset('storage/' . $item['imagen']) }}"
                                alt="{{ $item['nombre'] }}"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-contain p-1"
                            >

                        @else

                            <span class="px-1 text-center text-[10px] text-slate-400">
                                Sin imagen
                            </span>

                        @endif
                    </a>


                    {{-- NOMBRE Y PRECIO UNITARIO --}}
                    <div class="min-w-0 flex-1">

                        <h3 class="text-sm font-semibold leading-5 text-slate-900">
                            <a
                                href="{{ route('tienda.productos.show', $item['id']) }}"
                                title="{{ $item['nombre'] }}"
                                class="line-clamp-2 break-words hover:text-blue-600"
                            >
                                {{ $item['nombre'] }}
                            </a>
                        </h3>

                        <p class="mt-2 text-xs text-slate-500">
                            S/ {{ number_format($item['precio'], 2) }}
                            <span>por unidad</span>
                        </p>

                    </div>

                </div>


                {{-- CANTIDAD, SUBTOTAL Y ELIMINACIÓN --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">

                    {{-- Indicador de cantidad, no modifica las unidades. --}}
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-600">
                        Cantidad:
                        <strong class="text-slate-900">
                            {{ $item['cantidad'] }}
                        </strong>
                    </span>

                    <p class="text-sm font-bold text-slate-950">
                        S/ {{ number_format($subtotal, 2) }}
                    </p>

                    {{-- Conserva tu ruta actual de eliminación. --}}
                    <form
                        action="{{ url('/carrito/eliminar/' . $item['id']) }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            aria-label="Quitar {{ $item['nombre'] }} del carrito"
                            class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-lg px-2 text-xs font-medium text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline-2 focus-visible:outline-blue-600"
                        >
                            <svg
                                aria-hidden="true"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6M14 10v6"
                                />
                            </svg>

                            Quitar
                        </button>
                    </form>

                </div>

            </article>

        @endforeach

    </div>


    {{-- TOTAL Y ACCESO AL CARRITO COMPLETO --}}
    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-900">
                    Total de productos
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $cantidadTotal }}
                    {{ $cantidadTotal == 1 ? 'unidad en tu carrito' : 'unidades en tu carrito' }}
                </p>
            </div>

            <p class="text-2xl font-extrabold tracking-tight text-slate-950">
                S/ {{ number_format($total, 2) }}
            </p>
        </div>

        <a
            href="{{ url('/carrito') }}"
            class="mt-5 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
        >
            Ver carrito completo

            <svg
                aria-hidden="true"
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 12h14m-6-6 6 6-6 6"
                />
            </svg>
        </a>

        <a
            href="{{ url('/productos') }}"
            class="mt-2 flex min-h-11 items-center justify-center rounded-lg text-sm font-medium text-slate-600 transition-colors hover:text-blue-600"
        >
            Seguir explorando
        </a>

    </div>

@else

    {{-- CARRITO VACÍO --}}
    <div class="flex flex-col items-center px-4 py-10 text-center">

        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-blue-600">
            <svg
                aria-hidden="true"
                class="h-9 w-9"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 3h2l2.4 12h11.2L21 7H6M9 20h.01M17 20h.01"
                />
            </svg>
        </div>

        <h3 class="mt-5 text-lg font-bold text-slate-900">
            Tu carrito está vacío
        </h3>

        <p class="mt-2 max-w-xs text-sm leading-6 text-slate-500">
            Encuentra los componentes para tu próxima mejora
            y agrégalos aquí.
        </p>

        <a
            href="{{ url('/productos') }}"
            class="mt-6 inline-flex min-h-12 items-center justify-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
        >
            Explorar productos
        </a>

    </div>

@endif