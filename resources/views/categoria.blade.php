{{-- Navbar, carrito y footer compartidos con el catálogo. --}}
@extends('layouts.app')

@section('title', $categoria->nombre . ' | ByteStore')

{{-- No incluimos la sección banner en esta página. --}}

@section('contenido')
    @include('partials.form-errors')

    {{-- =====================================================
        ENCABEZADO COMPACTO
        Mantiene el estilo de "Todos los productos".
    ===================================================== --}}
    <header class="mb-4 border-b border-slate-200 pb-4 sm:mb-6 sm:pb-6">

        <a
            href="{{ url('/productos') }}"
            class="text-xs font-medium text-slate-500 hover:text-blue-600"
        >
            ← Volver a todos los productos
        </a>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">

            <h1 class="min-w-0 break-words text-xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                {{ $categoria->nombre }}
            </h1>

            <span class="shrink-0 text-xs font-medium text-slate-500">
                {{ $productos->total() }}
                {{ $productos->total() === 1 ? 'producto' : 'productos' }}
            </span>

        </div>

    </header>


    {{-- =====================================================
        CATEGORÍAS
        En celular se pueden deslizar horizontalmente.
    ===================================================== --}}
    <div class="catalog-toolbar mb-5 sm:mb-7">
    <nav
        aria-label="Categorías de productos"
        class="min-w-0 flex-1"
    >
        <p class="mb-2 text-xs text-slate-500 sm:hidden">
            Desliza para ver las categorías →
        </p>

        <div class="bs-category-tabs flex overflow-x-auto">

            <a
                href="{{ url('/productos') }}"
                class="bs-category-tab inline-flex min-h-11 shrink-0 items-center justify-center text-xs font-medium transition-colors sm:text-sm"
            >
                Todos
            </a>

            {{--
                Usamos $opcion para no sobrescribir $categoria,
                que contiene la categoría seleccionada.
            --}}
            @foreach ($categorias as $opcion)

                @php
                    $seleccionada = $opcion->id == $categoria->id;
                @endphp

                <a
                    href="{{ url('/categoria/' . $opcion->id) }}"
                    @if ($seleccionada) aria-current="page" @endif
                    @class([
                        'bs-category-tab inline-flex min-h-11 shrink-0 items-center justify-center whitespace-nowrap text-xs font-medium transition-colors sm:text-sm',
                        'is-active' => $seleccionada,
                        'text-slate-600 hover:text-blue-600' => !$seleccionada,
                    ])
                >
                    {{ $opcion->nombre }}
                </a>

            @endforeach

        </div>
    </nav>
    @include('partials.catalogo-filtros')
    </div>


    {{-- =====================================================
        PRODUCTOS
        Celular: 2 columnas.
        Tablet: 3 columnas.
        PC: 4 columnas.

        Aunque haya un solo producto, conserva el tamaño
        de una tarjeta y no ocupa todo el ancho.
    ===================================================== --}}
    <div class="bs-product-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

        @forelse ($productos as $producto)

            <article class="bs-product flex min-w-0 flex-col">

                {{-- FOTO: completa y sin recortes. --}}
                <a
                    href="{{ route('tienda.productos.show', $producto->id) }}"
                    target="_blank"
                    rel="noopener"
                    aria-label="Ver {{ $producto->nombre }} (abre otra pestaña)"
                    class="bs-product-image relative block w-full overflow-hidden bg-white"
                >
                    @if ($producto->imagen)

                        <img
                            src="{{ asset('storage/' . $producto->imagen) }}"
                            alt="{{ $producto->nombre }}"
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-0 h-full w-full object-contain p-2 sm:p-3"
                        >

                    @else

                        <div class="absolute inset-0 flex items-center justify-center bg-slate-50 p-3 text-center text-xs text-slate-500">
                            Imagen no disponible
                        </div>

                    @endif
                </a>


                {{-- INFORMACIÓN BREVE --}}
                <div class="bs-product-info flex min-w-0 flex-1 flex-col">

                    <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">
                        {{ $categoria->nombre }}
                    </p>

                    {{-- Nombre limitado visualmente a dos líneas. --}}
                    <h2 class="mt-2 min-h-10 text-sm font-semibold leading-5 text-slate-900 sm:min-h-12 sm:text-base sm:leading-6">

                        <a
                            href="{{ route('tienda.productos.show', $producto->id) }}"
                            target="_blank"
                            rel="noopener"
                            title="{{ $producto->nombre }}"
                            class="line-clamp-2 break-words hover:text-blue-600"
                        >
                            {{ $producto->nombre }}

                            <span class="sr-only">(abre otra pestaña)</span>
                        </a>

                    </h2>

                    {{--
                        No mostramos $producto->descripcion aquí.
                        La descripción completa permanece en el detalle.
                    --}}

                    <div class="mt-auto pt-3">

                        {{-- Precio con dos decimales. --}}
                        <p class="bs-product-price text-base font-extrabold text-slate-950 sm:text-xl">
                            S/ {{ number_format($producto->precio, 2) }}
                        </p>

                        {{-- Disponibilidad breve. --}}
                        <p class="mt-2 text-[11px] font-medium leading-4 sm:text-xs">
                            @if ($producto->stock > 10)
                                <span class="text-slate-600">
                                    Disponible
                                </span>
                            @elseif ($producto->stock > 0)
                                <span class="text-slate-600">
                                    Últimas unidades
                                </span>
                            @else
                                <span class="text-slate-400">
                                    Agotado
                                </span>
                            @endif
                        </p>

                        {{-- Acceso a fotos y especificaciones completas. --}}
                        <a
                            href="{{ route('tienda.productos.show', $producto->id) }}"
                            target="_blank"
                            rel="noopener"
                            class="bs-product-detail mt-3 inline-flex min-h-11 items-center gap-2 text-xs font-semibold text-slate-600 transition-colors hover:text-blue-600 sm:text-sm"
                        >
                            Ver detalle
                            <span class="sr-only">(abre otra pestaña)</span>
                        </a>

                        {{-- Conserva la conexión con el JavaScript del carrito. --}}
                        <form
                            class="form-agregar-carrito mt-2"
                            data-id="{{ $producto->id }}"
                            action="{{ url('/carrito/agregar/' . $producto->id) }}"
                            method="POST"
                        >
                            @csrf

                            @if ($producto->stock > 0)

                                <button
                                    type="submit"
                                    class="flex min-h-11 w-full items-center justify-center rounded-lg bg-blue-600 px-2 py-2 text-center text-xs font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60 sm:text-sm"
                                >
                                    Agregar al carrito
                                </button>

                            @else

                                <button
                                    type="button"
                                    disabled
                                    class="flex min-h-11 w-full cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-2 py-2 text-center text-xs font-semibold text-slate-400 sm:text-sm"
                                >
                                    Agotado
                                </button>

                            @endif
                        </form>

                    </div>
                </div>

            </article>

        @empty

            {{-- Mensaje cuando la categoría todavía no tiene productos. --}}
            <div class="col-span-full py-10 text-center sm:py-12">

                <h2 class="text-base font-semibold text-slate-900 sm:text-lg">
                    Aún no hay productos en esta categoría
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Puedes explorar los componentes de las demás categorías.
                </p>

                <a
                    href="{{ url('/productos') }}"
                    class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Ver todos los productos
                </a>

            </div>

        @endforelse

    </div>


    {{-- PAGINACIÓN --}}
    @if ($productos->hasPages())
        <div class="mt-8 min-w-0 border-t border-slate-200 pt-6">
            {{ $productos->links() }}
        </div>
    @endif

@endsection
