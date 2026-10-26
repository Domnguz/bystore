@extends('layouts.app')

@section('contenido')

{{-- Calculamos las unidades y el total usando el carrito actual. --}}
@php
    $cantidadTotal = collect($carrito)->sum('cantidad');

    $total = collect($carrito)->sum(function ($item) {
        return $item['precio'] * $item['cantidad'];
    });
@endphp

<div class="mx-auto w-full max-w-6xl">
    @include('partials.form-errors')

    {{-- ENCABEZADO: pequeño en celular y más amplio en computadora. --}}
    <header class="mb-6 sm:mb-8">
        <a
            href="{{ url('/productos') }}"
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

            Seguir comprando
        </a>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                Mi carrito
            </h1>

            <span class="rounded-full border border-gray-200 bg-white
                         px-3 py-1.5 text-xs font-medium text-gray-600 sm:text-sm">
                {{ $cantidadTotal }}
                {{ $cantidadTotal == 1 ? 'unidad' : 'unidades' }}
            </span>
        </div>

        <p class="mt-2 text-sm leading-6 text-gray-500">
            Revisa tus productos antes de continuar con el pedido.
        </p>
    </header>

    {{-- MENSAJE: muestra los errores enviados por el controlador. --}}
    @if (session('error'))
        <div
            role="alert"
            class="mb-5 rounded-xl border border-red-200 bg-red-50
                   p-4 text-sm text-red-700"
        >
            {{ session('error') }}
        </div>
    @endif

    @if (count($carrito) > 0)

        {{-- DISTRIBUCIÓN:
             Celular: productos y después resumen.
             Computadora: productos a la izquierda y resumen a la derecha. --}}
        <div class="grid items-start gap-5 lg:grid-cols-3 lg:gap-8">

            {{-- LISTADO DE PRODUCTOS --}}
            <section
                aria-label="Productos de tu carrito"
                class="min-w-0 space-y-3 lg:col-span-2"
            >
                @foreach ($carrito as $item)

                    @php
                        $subtotal = $item['precio'] * $item['cantidad'];
                    @endphp

                    <article class="rounded-2xl border border-gray-200
                                    bg-white p-3 sm:p-5">

                        <div class="flex items-start gap-3 sm:gap-5">

                            {{-- FOTO:
                                 object-contain muestra el producto completo. --}}
                            <a
                                href="{{ route('tienda.productos.show', $item['id']) }}"
                                aria-label="Ver {{ $item['nombre'] }}"
                                class="flex h-20 w-20 shrink-0 items-center
                                       justify-center overflow-hidden rounded-xl
                                       border border-gray-100 bg-white
                                       sm:h-28 sm:w-28"
                            >
                                @if (!empty($item['imagen']))
                                    <img
                                        src="{{ asset('storage/' . $item['imagen']) }}"
                                        alt="{{ $item['nombre'] }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-contain p-2"
                                    >
                                @else
                                    <span class="px-2 text-center text-xs text-gray-400">
                                        Sin imagen
                                    </span>
                                @endif
                            </a>

                            {{-- INFORMACIÓN:
                                 min-w-0 permite ajustar nombres largos. --}}
                            <div class="min-w-0 flex-1">
                                <h2 class="break-words text-sm font-semibold
                                           leading-5 text-gray-900 sm:text-base sm:leading-6">
                                    <a
                                        href="{{ route('tienda.productos.show', $item['id']) }}"
                                        class="transition hover:text-blue-600"
                                    >
                                        {{ $item['nombre'] }}
                                    </a>
                                </h2>

                                <p class="mt-2 text-xs text-gray-500 sm:text-sm">
                                    Precio unitario
                                </p>

                                <p class="mt-0.5 text-sm font-semibold text-gray-700">
                                    S/ {{ number_format($item['precio'], 2) }}
                                </p>
                            </div>
                        </div>

                        {{-- PARTE INFERIOR: cantidad, subtotal y eliminar. --}}
                        <div class="mt-4 flex flex-wrap items-center
                                    justify-between gap-3 border-t
                                    border-gray-100 pt-3">

                            <div class="flex flex-wrap items-center gap-3 sm:gap-5">
                                <form action="{{ url('/carrito/actualizar/'.$item['id']) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <label for="cantidad-{{ $item['id'] }}" class="text-sm">Cantidad</label>
                                    <input id="cantidad-{{ $item['id'] }}" type="number" name="cantidad" min="1" max="{{ min($item['stock'] ?? 99, 99) }}" value="{{ $item['cantidad'] }}" required class="w-20 rounded-lg border border-slate-300 p-2">
                                    <button class="min-h-11 rounded-lg border border-blue-200 px-3 text-sm text-blue-700">Actualizar</button>
                                </form>

                                <div>
                                    <p class="text-xs text-gray-500">
                                        Subtotal
                                    </p>

                                    <p class="text-base font-bold text-gray-900 sm:text-lg">
                                        S/ {{ number_format($subtotal, 2) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Conservamos la ruta actual para eliminar. --}}
                            <form
                                action="{{ url('/carrito/eliminar/' . $item['id']) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    aria-label="Eliminar {{ $item['nombre'] }} del carrito"
                                    class="inline-flex min-h-11 items-center
                                           justify-center gap-2 rounded-lg px-3
                                           text-xs font-medium text-gray-500
                                           transition hover:bg-red-50
                                           hover:text-red-600
                                           focus-visible:outline
                                           focus-visible:outline-2
                                           focus-visible:outline-blue-600"
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
                                            d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6M14 10v6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </article>

                @endforeach
            </section>

            {{-- RESUMEN:
                 Solo se mantiene fijo al desplazarse en pantallas grandes.
                 top-24 deja espacio para el navbar. --}}
            <aside
                aria-labelledby="titulo-resumen"
                class="min-w-0 rounded-2xl border border-gray-200
                       bg-white p-5 sm:p-6 lg:sticky lg:top-24"
            >
                <h2
                    id="titulo-resumen"
                    class="text-lg font-bold text-gray-900"
                >
                    Resumen del pedido
                </h2>

                <div class="mt-5 flex items-center justify-between
                            gap-3 text-sm text-gray-500">
                    <span>Unidades</span>

                    <span class="font-medium text-gray-900">
                        {{ $cantidadTotal }}
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between
                            gap-3 border-t border-gray-100 pt-4">
                    <span class="font-semibold text-gray-900">
                        Total
                    </span>

                    <span class="text-2xl font-bold tracking-tight text-blue-600">
                        S/ {{ number_format($total, 2) }}
                    </span>
                </div>

                {{-- Usuario conectado: puede continuar al checkout. --}}
                @auth
                    <a
                        href="{{ url('/checkout') }}"
                        class="mt-6 flex min-h-12 w-full items-center
                               justify-center gap-2 rounded-xl bg-blue-600
                               px-4 py-3 text-center text-sm font-semibold
                               text-white transition hover:bg-blue-700"
                    >
                        Continuar con mi pedido

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                d="M5 12h14m-7-7 7 7-7 7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>

                {{-- Visitante: primero debe iniciar sesión. --}}
                @else
                    <p class="mt-5 rounded-xl bg-blue-50 p-3
                              text-sm leading-6 text-blue-800">
                        Inicia sesión para continuar con tu pedido.
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="mt-4 flex min-h-12 w-full items-center
                               justify-center rounded-xl bg-blue-600
                               px-4 py-3 text-sm font-semibold text-white
                               transition hover:bg-blue-700"
                    >
                        Iniciar sesión
                    </a>
                @endauth

                <a
                    href="{{ url('/productos') }}"
                    class="mt-2 flex min-h-11 items-center justify-center
                           text-sm font-medium text-gray-500
                           transition hover:text-blue-600"
                >
                    Explorar más productos
                </a>
            </aside>
        </div>

    @else

        {{-- CARRITO VACÍO: también ofrece una salida al catálogo. --}}
        <section class="rounded-2xl border border-dashed border-gray-300
                        bg-white px-5 py-12 text-center sm:py-16">

            <div class="mx-auto flex h-16 w-16 items-center
                        justify-center rounded-full bg-blue-50 text-blue-600">
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
                Tu carrito está vacío
            </h2>

            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">
                Encuentra los componentes para tu próxima mejora.
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

@endsection
