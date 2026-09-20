{{-- Usa el layout que contiene navbar, carrito y footer. --}}
@extends('layouts.app')

@section('title', 'Productos | ByteStore')

{{-- El banner usa una sección independiente para tener más ancho. --}}
@section('banner')
    @include('partials.banner-carrusel', ['banners' => $banners])
@endsection
@section('contenido')



 {{-- Encabezado compacto, adaptado a celular y PC. --}}
<header class="mb-4 border-b border-slate-200 pb-4 sm:mb-6 sm:pb-6">

    <p class="text-[10px] font-semibold uppercase tracking-widest text-blue-600 sm:text-xs">
        ByteStore / Catálogo
    </p>

    <div class="mt-2 flex items-center justify-between gap-3">

        <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
            Todos los productos
        </h1>

        <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
            {{ $productos->total() }}
            {{ $productos->total() === 1 ? 'producto' : 'productos' }}
        </span>

    </div>

    <p class="mt-2 max-w-xl text-xs leading-5 text-slate-500 sm:text-sm sm:leading-6">
        Componentes y accesorios para construir y mejorar tu PC.
    </p>

</header>


    {{-- =====================================================
        2. NAVEGACIÓN DE CATEGORÍAS

        overflow-x-auto permite deslizar las categorías en celular.
        shrink-0 evita que cada enlace se comprima.
        No se necesitan scripts para este desplazamiento.
    ===================================================== --}}
    <nav
        aria-label="Filtrar productos por categoría"
        class="mb-6 min-w-0 sm:mb-8"
    >

        <p class="mb-3 text-xs text-slate-500 sm:hidden">
            Desliza para ver las categorías →
        </p>

        <div class="flex gap-2 overflow-x-auto pb-3">

            {{-- Categoría activa: estamos viendo todos los productos. --}}
            <a
                href="{{ url('/productos') }}"
                aria-current="page"
                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full bg-blue-600 px-5 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            >
                Todos
            </a>

            {{-- Genera un enlace por cada categoría registrada en el admin. --}}
            @foreach ($categorias as $categoria)

                <a
                    href="{{ url('/categoria/' . $categoria->id) }}"
                    class="inline-flex min-h-11 shrink-0 items-center justify-center whitespace-nowrap rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 transition-colors hover:border-blue-600 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                >
                    {{ $categoria->nombre }}
                </a>

            @endforeach

        </div>
    </nav>


    {{-- =====================================================
        3. CUADRÍCULA DE PRODUCTOS

        grid-cols-2: dos columnas en celular.
        md:grid-cols-3: tres columnas desde tablet.
        lg:grid-cols-4: cuatro columnas en PC.

        Este contenedor envuelve todas las tarjetas.
    ===================================================== --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">

        {{-- Repite la tarjeta para cada producto de la página actual. --}}
        @forelse ($productos as $producto)

            <article class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">

                {{-- =========================================
                    FOTO PRINCIPAL

                    aspect-square crea un espacio cuadrado.
                    object-contain muestra la imagen completa.
                    Al tocar la foto se abre el detalle.
                ========================================= --}}
                <a
                    href="{{ route('tienda.productos.show', $producto->id) }}"
                    target="_blank"
                    rel="noopener"
                    aria-label="Ver {{ $producto->nombre }} (abre otra pestaña)"
                    class="relative block aspect-square w-full overflow-hidden bg-white focus-visible:outline-2 focus-visible:outline-blue-600"
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

                        {{-- Se muestra si no existe una foto registrada. --}}
                        <div class="absolute inset-0 flex items-center justify-center bg-slate-50 p-3 text-center text-xs text-slate-500">
                            Imagen no disponible
                        </div>

                    @endif

                </a>


                {{-- =========================================
                    INFORMACIÓN DEL PRODUCTO

                    flex-1 permite aprovechar el espacio disponible.
                    Los tamaños aumentan en pantallas más grandes.
                ========================================= --}}
                <div class="flex min-w-0 flex-1 flex-col p-3 sm:p-4">

                    {{-- Nombre de la categoría relacionada con el producto. --}}
                    <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">
                        {{ $producto->categoria?->nombre ?? 'Componentes para PC' }}
                    </p>

                    {{--
                        NOMBRE

                        line-clamp-2 limita el nombre a dos líneas.
                        min-h-10 reserva espacio para mantener la alineación.
                        El nombre completo aparece en la página de detalle.
                    --}}
                    <h2 class="mt-2 min-h-10 text-sm font-semibold leading-5 text-slate-900 sm:min-h-12 sm:text-base sm:leading-6">

                        <a
                            href="{{ route('tienda.productos.show', $producto->id) }}"
                            target="_blank"
                            rel="noopener"
                            title="{{ $producto->nombre }}"
                            class="line-clamp-2 break-words transition-colors hover:text-blue-600"
                        >
                            {{ $producto->nombre }}

                            <span class="sr-only">(abre otra pestaña)</span>
                        </a>

                    </h2>


                    {{-- mt-auto alinea la parte inferior de las tarjetas. --}}
                    <div class="mt-auto pt-3">

                        {{-- PRECIO: dos decimales y separador de miles. --}}
                        <p class="text-base font-extrabold text-slate-950 sm:text-xl">
                            S/ {{ number_format($producto->precio, 2) }}
                        </p>

                        {{-- DISPONIBILIDAD: se calcula con el stock registrado. --}}
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


                        {{-- ENLACE A LA GALERÍA Y DESCRIPCIÓN COMPLETA --}}
                        <a
                            href="{{ route('tienda.productos.show', $producto->id) }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-3 flex min-h-11 w-full items-center justify-center rounded-lg border border-slate-200 px-2 py-2 text-center text-xs font-semibold text-slate-700 transition-colors hover:border-blue-600 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:text-sm"
                        >
                            Ver detalle

                            <span class="sr-only">(abre otra pestaña)</span>
                        </a>


                        {{-- =================================
                            AGREGAR AL CARRITO

                            Conserva la clase y data-id usados
                            por el JavaScript actual de la tienda.

                            El formulario envía el ID del producto.
                            El servidor debe comprobar el stock.
                        ================================= --}}
                        <form
                            class="form-agregar-carrito mt-2"
                            data-id="{{ $producto->id }}"
                            action="{{ url('/carrito/agregar/' . $producto->id) }}"
                            method="POST"
                        >

                            {{-- Token de protección que Laravel requiere en POST. --}}
                            @csrf

                            @if ($producto->stock > 0)

                                <button
                                    type="submit"
                                    class="flex min-h-11 w-full items-center justify-center rounded-lg bg-blue-600 px-2 py-2 text-center text-xs font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-wait disabled:opacity-60 sm:text-sm"
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
                    {{-- Fin del precio, disponibilidad y acciones. --}}

                </div>
                {{-- Fin de la información del producto. --}}

            </article>
            {{-- Fin de una tarjeta. --}}

        @empty

            {{-- Se muestra cuando la consulta no contiene productos. --}}
            <div class="col-span-full rounded-xl border border-slate-200 bg-white px-6 py-12 text-center">

                <h2 class="text-xl font-semibold text-slate-900">
                    Todavía no hay productos
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Estamos preparando nuestro catálogo de componentes
                    y accesorios para tu PC.
                </p>

            </div>

        @endforelse

    </div>
    {{-- Fin de la cuadrícula. --}}


    {{-- =====================================================
        4. PAGINACIÓN

        Muestra los enlaces para avanzar entre páginas.
        Funciona porque el controlador utiliza paginate(12).
    ===================================================== --}}
    @if ($productos->hasPages())

        <div class="mt-8 min-w-0 border-t border-slate-200 pt-6">
            {{ $productos->links() }}
        </div>

    @endif

@endsection