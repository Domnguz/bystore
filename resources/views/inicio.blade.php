{{-- =========================================================
    ARCHIVO: resources/views/inicio.blade.php

    Esta es la página de inicio de ByteStore.

    Contiene:
    1. Portada con video, logo y presentación.
    2. Enlaces opcionales a redes sociales.
    3. Productos destacados.
    4. Enlace al catálogo completo.
    5. Información de servicios.

    Los productos llegan desde la ruta que muestra el inicio.
========================================================= --}}


{{-- Hereda el diseño general que contiene navbar, carrito y footer. --}}
@extends('layouts.app')

{{-- El layout debe tener @yield('title') para mostrar este título. --}}
@section('title', 'ByteStore | Componentes y servicio técnico')


{{-- =========================================================
    1. PORTADA

    El layout coloca este contenido donde tiene:
    @yield('banner')

    Se mantiene fuera del contenedor del catálogo para que
    el video pueda ocupar todo el ancho de la pantalla.
========================================================= --}}
@section('banner')

    {{--
        id="inicio":
        Nuestro JavaScript utiliza este identificador para
        controlar la visibilidad del navbar y del carrito.

        min-h-svh:
        La portada ocupa como mínimo la altura visible
        del celular y puede crecer si el contenido lo necesita.

        relative:
        Permite colocar el video y la capa oscura detrás del texto.

        isolate:
        Mantiene las capas de esta sección dentro de ella.
    --}}
    <section
        id="inicio"
        aria-labelledby="tituloInicio"
        class="relative isolate flex min-h-svh w-full items-center overflow-hidden bg-slate-950"
    >

        {{--
            VIDEO DE FONDO

            autoplay: intenta reproducirse automáticamente.
            muted: comienza sin sonido.
            loop: vuelve a empezar cuando termina.
            playsinline: permite reproducirlo dentro de la página en móvil.
            preload="metadata": solicita inicialmente los metadatos;
            al reproducirse, el navegador descarga el video.

            object-cover llena el fondo, aunque recorte sus bordes.
            Es adecuado para el fondo; en productos usamos object-contain.
        --}}
        <video
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
        >
            {{-- asset() genera la dirección de un archivo dentro de public. --}}
            <source
                src="{{ asset('videos/hero.mp4') }}"
                type="video/mp4"
            >
        </video>

        {{-- CAPA OSCURA: mejora el contraste del texto sobre el video. --}}
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 -z-10 bg-slate-950/70"
        ></div>

        {{--
            DISTRIBUCIÓN DE LA PORTADA

            Celular: una columna.
            PC, desde lg: dos columnas.

            max-w-7xl limita el ancho del contenido.
            mx-auto centra ese contenido.
            px y py agregan espacio horizontal y vertical.
        --}}
        <div class="mx-auto grid w-full max-w-7xl items-center gap-8 px-5 py-12 sm:gap-10 sm:px-8 sm:py-16 lg:grid-cols-2 lg:gap-8 lg:px-10 lg:py-20">

            {{--
                LOGO

                Aparece primero, arriba, en celular.
                lg:order-2 lo coloca en la segunda columna en PC.
                h-auto conserva las proporciones de la imagen.
            --}}
            <div class="flex min-w-0 items-center justify-center lg:order-2 lg:justify-end">
                <img
                    src="{{ asset('img/ByteStore-sindescripcion.png') }}"
                    alt="ByteStore"
                    fetchpriority="high"
                    class="h-auto w-72 max-w-full object-contain sm:w-96 lg:w-full lg:max-w-[580px]"
                >
            </div>

            {{--
                PRESENTACIÓN

                Celular: texto centrado debajo del logo.
                PC: texto alineado a la izquierda.
                min-w-0 permite que la columna se reduzca correctamente.
            --}}
            <div class="flex min-w-0 flex-col items-center text-center lg:order-1 lg:items-start lg:text-left">

                {{-- Texto introductorio de la marca. --}}
                <p class="max-w-sm text-xs font-semibold uppercase leading-6 tracking-[0.14em] text-blue-200 sm:text-sm">
                    Componentes para PC

                    <span aria-hidden="true" class="mx-1">·</span>

                    Servicio técnico
                </p>

                {{--
                    TÍTULO PRINCIPAL

                    Solo usamos un h1 en esta página.
                    sm y xl aumentan el tamaño en pantallas más grandes.
                    text-wrap:balance distribuye mejor las líneas.
                --}}
                <h1
                    id="tituloInicio"
                    class="mt-4 max-w-[18ch] text-4xl font-extrabold leading-[1.1] tracking-tight text-white [text-wrap:balance] sm:text-5xl xl:text-6xl"
                >
                    Tu próxima mejora

                    <span class="mt-2 block text-blue-400">
                        empieza aquí.
                    </span>
                </h1>

                {{-- Descripción breve del emprendimiento. --}}
                <p class="mt-5 max-w-lg text-sm leading-7 text-slate-200 sm:text-base lg:text-lg lg:leading-8">
                    Componentes para trabajar, estudiar y jugar.
                    Asesoría y mantenimiento para que tu equipo
                    rinda al máximo.
                </p>

                {{--
                    ENLACES DE LA PORTADA

                    flex-col: uno debajo del otro en celular.
                    sm:flex-row: uno al lado del otro desde pantallas pequeñas.
                --}}
                <div class="mt-8 flex w-full flex-col items-center gap-5 sm:flex-row sm:justify-center sm:gap-7 lg:justify-start">

                    {{-- Abre la página que lista todos los productos. --}}
                    <a
                        href="{{ url('/productos') }}"
                        class="group inline-flex min-h-14 w-full items-center justify-between gap-6 rounded-full bg-blue-600 py-2 pl-7 pr-2 text-white shadow-lg shadow-blue-950/30 transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300 sm:w-auto"
                    >
                        <span class="text-sm font-semibold tracking-wide">
                            Explorar productos
                        </span>

                        {{-- Círculo decorativo que contiene la flecha. --}}
                        <span
                            aria-hidden="true"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-blue-700"
                        >
                            {{-- SVG: dibuja el icono sin cargar una imagen externa. --}}
                            <svg
                                class="h-5 w-5 transition-transform motion-safe:group-hover:translate-x-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-6-6 6 6-6 6"
                                />
                            </svg>
                        </span>
                    </a>

                    {{-- El símbolo # lleva a una sección de esta misma página. --}}
                    <a
                        href="#catalogo"
                        class="group inline-flex min-h-12 items-center justify-center gap-3 rounded-full px-2 text-sm font-medium text-slate-200 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300"
                    >
                        <span class="decoration-blue-400 underline-offset-8 group-hover:underline">
                            Descubrir novedades
                        </span>

                        {{-- Flecha que apunta hacia el catálogo. --}}
                        <svg
                            class="h-4 w-4 text-blue-400 transition-transform motion-safe:group-hover:translate-y-1"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4v16m-6-6 6 6 6-6"
                            />
                        </svg>
                    </a>

                </div>
                {{-- Fin de los enlaces de la portada. --}}

            </div>
            {{-- Fin de la presentación. --}}

        </div>
        {{-- Fin de las columnas de la portada. --}}

    </section>

@endsection
{{-- Fin de la sección banner. --}}


{{-- =========================================================
    CONTENIDO DE LA PÁGINA

    El layout coloca todo lo siguiente en:
    @yield('contenido')
========================================================= --}}
@section('contenido')

    {{-- =====================================================
        2. REDES SOCIALES OPCIONALES

        Escribe las direcciones reales entre las comillas.
        Mientras estén vacías, no se mostrarán enlaces.
    ===================================================== --}}
    @php
        $redes = [
            'Instagram' => '',
            'Facebook' => '',
            'Soporte' => '',
        ];
    @endphp

    {{-- array_filter elimina valores vacíos para comprobar si hay enlaces. --}}
    @if (count(array_filter($redes)) > 0)

        <nav
            aria-label="Redes sociales"
            class="mx-auto mb-8 flex w-fit max-w-full flex-wrap justify-center gap-3"
        >
            {{-- Recorre las redes: obtiene el nombre y su dirección. --}}
            @foreach ($redes as $nombre => $enlace)

                {{-- Solo muestra esta red si tiene una dirección. --}}
                @if ($enlace)

                    <a
                        href="{{ $enlace }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-600 transition-colors hover:border-blue-600 hover:text-blue-600"
                    >
                        {{ $nombre }}

                        {{-- sr-only: texto disponible para lectores de pantalla. --}}
                        <span class="sr-only">(abre otra pestaña)</span>
                    </a>

                @endif

            @endforeach
        </nav>

    @endif


    {{-- =====================================================
        3. PRODUCTOS DESTACADOS

        $productos contiene los productos enviados desde Laravel.
        Esta vista los muestra; no los crea ni modifica.

        id="catalogo" es el destino del enlace de la portada.
        scroll-mt-24 deja espacio para el navbar al llegar aquí.
    ===================================================== --}}
    <section
        id="catalogo"
        aria-labelledby="tituloCatalogo"
        class="scroll-mt-24 space-y-8 pt-8 sm:space-y-12 sm:pt-12"
    >

        {{-- ENCABEZADO DEL CATÁLOGO --}}
        <div class="space-y-3 text-center">

            <p class="text-xs font-bold uppercase tracking-widest text-blue-600 sm:text-sm">
                Catálogo
            </p>

            <h2
                id="tituloCatalogo"
                class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl"
            >
                Potencia tu PC con nuestras novedades
            </h2>

            <p class="mx-auto max-w-xl text-sm leading-7 text-slate-500 sm:text-lg">
                Conoce nuestros ultimos productos y servicios para mejorar tu computadora.
            </p>

        </div>

        {{--
            CUADRÍCULA RESPONSIVE

            grid: organiza las tarjetas en filas y columnas.
            grid-cols-2: dos productos por fila en celular.
            md:grid-cols-3: tres desde 768 px, con Tailwind por defecto.
            lg:grid-cols-4: cuatro desde 1024 px.

            gap-3: separación compacta en celular.
            md:gap-5: mayor separación en pantallas grandes.

            Este contenedor envuelve TODAS las tarjetas.
        --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">

            {{--
                Repite una tarjeta por cada producto.

                $productos: colección completa.
                $producto: producto que se está mostrando en esta vuelta.

                Si no hay productos, se ejecuta el bloque @empty.
            --}}
            @forelse ($productos as $producto)

                {{--
                    TARJETA

                    flex-col: imagen e información una debajo de otra.
                    min-w-0: evita que un nombre largo ensanche la tarjeta.
                    overflow-hidden: respeta sus bordes redondeados.
                --}}
                <article class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">

                    {{--
                        ENLACE DE LA FOTO

                        route() genera la dirección del detalle.
                        El ID identifica qué producto debe abrir Laravel.
                        target="_blank" abre el detalle en otra pestaña.

                        aspect-square mantiene un espacio cuadrado.
                    --}}
                    <a
                        href="{{ route('tienda.productos.show', $producto->id) }}"
                        target="_blank"
                        rel="noopener"
                        aria-label="Ver {{ $producto->nombre }} (abre otra pestaña)"
                        class="relative block aspect-square w-full overflow-hidden bg-white focus-visible:outline-2 focus-visible:outline-blue-600"
                    >

                        {{-- Comprueba si el producto tiene una foto principal. --}}
                        @if ($producto->imagen)

                            {{--
                                FOTO DEL PRODUCTO

                                asset('storage/...') genera su URL pública.
                                object-contain muestra la foto completa.
                                p-2 agrega un margen pequeño alrededor.
                                loading="lazy" retrasa la carga si está lejos
                                de la parte visible de la página.

                                absolute + inset-0 hace que la imagen ocupe
                                el espacio cuadrado sin aumentar su altura.
                            --}}
                            <img
                                src="{{ asset('storage/' . $producto->imagen) }}"
                                alt="{{ $producto->nombre }}"
                                loading="lazy"
                                decoding="async"
                                class="absolute inset-0 h-full w-full object-contain p-2 sm:p-3"
                            >

                        @else

                            {{-- Mensaje para productos sin foto registrada. --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-50 p-3 text-center text-xs text-slate-500">
                                Imagen no disponible
                            </div>

                        @endif

                    </a>

                    {{-- INFORMACIÓN: espacios reducidos en celular. --}}
                    <div class="flex min-w-0 flex-1 flex-col p-3 sm:p-4">

                        {{--
                            CATEGORÍA

                            categoria es la relación del modelo Producto.
                            ?-> permite que no exista una categoría.
                            ?? establece un texto alternativo.

                            truncate limita el texto a una línea.
                        --}}
                        <p class="truncate text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-xs">
                            {{ $producto->categoria?->nombre ?? 'Componentes para PC' }}
                        </p>

                        {{--
                            NOMBRE

                            line-clamp-2 muestra hasta dos líneas.
                            El nombre completo sigue disponible en el detalle.
                            title permite verlo al mantener el cursor encima.

                            El tamaño pequeño evita tarjetas muy altas en móvil.
                        --}}
                        <h3 class="mt-2 text-sm font-semibold leading-5 text-slate-900 sm:text-base sm:leading-6">

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

                        </h3>

                        {{--
                            PRECIO Y BOTÓN

                            mt-auto aprovecha el espacio disponible y empuja
                            este bloque hacia abajo para alinear las tarjetas.
                        --}}
                        <div class="mt-auto pt-3">

                            {{-- number_format(..., 2) muestra dos decimales. --}}
                            <p class="text-base font-extrabold text-slate-950 sm:text-xl">
                                S/ {{ number_format($producto->precio, 2) }}
                            </p>

                            {{--
                                Abre el detalle con la galería del producto.
                                min-h-12 mantiene una altura cómoda para tocar.
                                focus-visible muestra el foco al usar teclado.
                            --}}
                            <a
                                href="{{ route('tienda.productos.show', $producto->id) }}"
                                target="_blank"
                                rel="noopener"
                                class="mt-3 flex min-h-12 w-full items-center justify-center rounded-lg bg-blue-600 px-2 py-2 text-center text-xs font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:text-sm"
                            >
                                Ver producto

                                <span class="sr-only">(abre otra pestaña)</span>
                            </a>

                        </div>
                        {{-- Fin del precio y botón. --}}

                    </div>
                    {{-- Fin de la información. --}}

                </article>
                {{-- Fin de una tarjeta. --}}

            @empty

                {{-- col-span-full hace que el mensaje ocupe todas las columnas. --}}
                <div class="col-span-full rounded-xl border border-slate-200 bg-white px-6 py-10 text-center">

                    <h3 class="text-lg font-semibold text-slate-900">
                        Próximamente encontrarás nuestros productos
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Estamos preparando el catálogo de ByteStore.
                    </p>

                </div>

            @endforelse
            {{-- Fin del recorrido de productos. --}}

        </div>
        {{-- Fin de la cuadrícula. --}}

    </section>
    {{-- Fin del catálogo de novedades. --}}


    {{-- =====================================================
        4. INVITACIÓN AL CATÁLOGO COMPLETO

        En celular: texto y enlace uno debajo del otro.
        Desde md: se colocan uno al lado del otro.
    ===================================================== --}}
    <section
        aria-labelledby="tituloEquipamiento"
        class="mt-16 flex flex-col gap-8 rounded-3xl bg-slate-900 px-6 py-10 sm:px-10 md:flex-row md:items-center md:justify-between"
    >

        <div class="max-w-xl">

            <p class="text-xs font-bold uppercase tracking-widest text-blue-300">
                Equipa tu espacio
            </p>

            <h2
                id="tituloEquipamiento"
                class="mt-4 text-3xl font-extrabold text-white sm:text-4xl"
            >
                El siguiente paso para tu PC
            </h2>

            <p class="mt-4 text-base leading-7 text-slate-300">
                Encuentra componentes y periféricos para trabajar,
                estudiar y disfrutar de tus juegos.
            </p>

        </div>

        {{-- url() genera la dirección del catálogo público. --}}
        <a
            href="{{ url('/productos') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-xl bg-white px-6 py-4 text-sm font-bold text-slate-900 transition-colors hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300"
        >
            Ver catálogo completo
            <span aria-hidden="true" class="ml-2">→</span>
        </a>

    </section>


    {{-- =====================================================
        5. SERVICIOS

        Estos textos están escritos directamente en esta vista.
        No se obtienen de la tabla de productos.

        Puedes modificar sus títulos y descripciones aquí.
    ===================================================== --}}
    <section
        aria-labelledby="tituloServicios"
        class="mt-16"
    >

        {{-- Encabezado de los servicios. --}}
        <div class="mb-8">

            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                Más que componentes
            </p>

            <h2
                id="tituloServicios"
                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
            >
                Servicios para tu equipo
            </h2>

        </div>

        {{-- Una columna en celular y tres desde md. --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            {{-- SERVICIO 1: ASESORÍA --}}
            <article class="rounded-2xl border border-slate-200 bg-white p-7">

                {{-- Numeración decorativa. --}}
                <p aria-hidden="true" class="text-sm font-bold text-blue-600">
                    01
                </p>

                <h3 class="mt-4 text-xl font-bold text-slate-900">
                    Asesoría de componentes
                </h3>

                <p class="mt-3 text-sm leading-7 text-slate-500">
                    Orientación para elegir piezas según tu presupuesto,
                    el uso de tu computadora y su compatibilidad.
                </p>

            </article>

            {{-- SERVICIO 2: MANTENIMIENTO --}}
            <article class="rounded-2xl border border-slate-200 bg-white p-7">

                <p aria-hidden="true" class="text-sm font-bold text-blue-600">
                    02
                </p>

                <h3 class="mt-4 text-xl font-bold text-slate-900">
                    Mantenimiento
                </h3>

                <p class="mt-3 text-sm leading-7 text-slate-500">
                    Limpieza y revisión del equipo para identificar
                    problemas y cuidar su funcionamiento.
                </p>

            </article>

            {{-- SERVICIO 3: ARMADO Y ACTUALIZACIÓN --}}
            <article class="rounded-2xl border border-slate-200 bg-white p-7">

                <p aria-hidden="true" class="text-sm font-bold text-blue-600">
                    03
                </p>

                <h3 class="mt-4 text-xl font-bold text-slate-900">
                    Armado y actualización
                </h3>

                <p class="mt-3 text-sm leading-7 text-slate-500">
                    Ensamblaje de computadoras e instalación de
                    componentes para mejorar tu equipo.
                </p>

            </article>

        </div>
        {{-- Fin de las tarjetas de servicios. --}}

    </section>

@endsection
{{-- Fin del contenido. El layout se encarga del carrito y del footer. --}}