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
        class="bs-home-hero relative isolate flex min-h-svh w-full items-center overflow-hidden bg-slate-950"
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
            class="bs-hero-overlay pointer-events-none absolute inset-0 -z-10 bg-slate-950/70"
        ></div>

        {{--
            DISTRIBUCIÓN DE LA PORTADA

            Celular: logo y descripción en una columna.
            Tablet: logo centrado.
            PC, desde lg: dos columnas.

            max-w-7xl limita el ancho del contenido.
            mx-auto centra ese contenido.
            px y py agregan espacio horizontal y vertical.
        --}}
        <div class="bs-hero-content mx-auto grid w-full max-w-7xl items-center gap-8 px-5 py-12 sm:gap-10 sm:px-8 sm:py-16 lg:grid-cols-2 lg:gap-8 lg:px-10 lg:py-20">

            {{--
                LOGO

                Aparece centrado en la portada en celular.
                lg:order-2 lo coloca en la segunda columna en PC.
                h-auto conserva las proporciones de la imagen.
            --}}
            <div class="bs-hero-brand flex min-w-0 items-center justify-center lg:order-2 lg:justify-end">
                <img
                    src="{{ asset('img/ByteStore-sindescripcion.png') }}"
                    alt="ByteStore"
                    fetchpriority="high"
                    width="2078"
                    height="757"
                    class="bs-hero-logo h-auto w-[min(82vw,400px)] max-w-full object-contain lg:w-full lg:max-w-[580px]"
                >
            </div>

            {{-- Presentación exclusiva para celulares. --}}
            <div class="bs-mobile-intro">
                <p class="bs-hero-eyebrow">Componentes · Servicio técnico</p>
                <h2>Tu PC, <span>al siguiente nivel.</span></h2>
                <p class="bs-hero-description">
                    Componentes, periféricos y servicio técnico.
                    Te ayudamos a elegir lo que necesitas para trabajar,
                    estudiar y jugar.
                </p>
                <a href="#catalogo" class="bs-mobile-catalog-link">
                    Ver productos
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>

            {{--
                PRESENTACIÓN

                Celular: solo el título para lectores de pantalla.
                PC: texto alineado a la izquierda.
                min-w-0 permite que la columna se reduzca correctamente.
            --}}
            <div class="contents min-w-0 flex-col items-center text-center lg:order-1 lg:flex lg:items-start lg:text-left">

                {{-- Texto introductorio de la marca. --}}
                <p class="hidden max-w-sm text-xs font-semibold uppercase leading-6 tracking-[0.14em] text-blue-200 sm:text-sm lg:block">
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
                    class="sr-only font-extrabold tracking-tight text-white [text-wrap:balance] lg:not-sr-only lg:mt-4 lg:max-w-[18ch] lg:text-5xl lg:leading-[1.1] xl:text-6xl"
                >
                    Tu PC,

                    <span class="mt-2 block text-blue-400">
                        al siguiente nivel.
                    </span>
                </h1>

                {{-- Descripción breve del emprendimiento. --}}
                <p class="mt-5 hidden max-w-lg text-sm leading-7 text-slate-200 sm:text-base lg:block lg:text-lg lg:leading-8">
                    Componentes para trabajar, estudiar y jugar.
                    Asesoría y mantenimiento para que tu equipo
                    rinda al máximo.
                </p>

                {{--
                    ENLACES DE LA PORTADA

                    flex-col: uno debajo del otro en celular.
                    sm:flex-row: uno al lado del otro desde pantallas pequeñas.
                --}}
                <div class="mt-8 hidden w-full flex-col items-center gap-5 sm:flex-row sm:justify-center sm:gap-7 lg:flex lg:justify-start">

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

        {{-- En móvil, invita a bajar directamente a los productos. --}}
        <a
            href="#catalogo"
            class="bs-hero-scroll group absolute inset-x-0 bottom-[calc(2rem+env(safe-area-inset-bottom))] mx-auto flex min-h-12 w-fit flex-col items-center gap-3 px-6 py-2 text-sm text-white/90 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300 lg:hidden"
        >
            <span class="bs-default-scroll-label">Ver productos</span>
            <span class="bs-mobile-scroll-label">Descubre nuestros productos</span>
            <svg
                aria-hidden="true"
                class="h-6 w-6 transition-transform motion-safe:group-hover:translate-y-1"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M12 4v16m-6-6 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>

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
        <div class="bs-catalog-heading">

            <p class="text-xs font-bold uppercase tracking-widest text-blue-600 sm:text-sm">
                Catálogo
            </p>

            <h2
                id="tituloCatalogo"
                class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl"
            >
                Potencia tu PC con nuestras novedades
            </h2>

            <p class="max-w-xl text-sm leading-7 text-slate-500 sm:text-base">
                Conoce nuestros últimos productos y servicios para mejorar tu computadora.
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
        <div class="bs-product-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

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
                <article class="bs-product flex min-w-0 flex-col">

                    {{--
                        ENLACE DE LA FOTO

                        route() genera la dirección del detalle.
                        El ID identifica qué producto debe abrir Laravel.
                        target="_blank" abre el detalle en otra pestaña.

                        La altura de la imagen se adapta al ancho de la pantalla.
                    --}}
                    <a
                        href="{{ route('tienda.productos.show', $producto->id) }}"
                        target="_blank"
                        rel="noopener"
                        aria-label="Ver {{ $producto->nombre }} (abre otra pestaña)"
                        class="bs-product-image relative block w-full overflow-hidden bg-white focus-visible:outline-2 focus-visible:outline-blue-600"
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
                    <div class="bs-product-info flex min-w-0 flex-1 flex-col">

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
                            <p class="bs-product-price text-base font-extrabold text-slate-950 sm:text-xl">
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
                                class="bs-product-detail mt-3 inline-flex min-h-11 items-center gap-2 text-xs font-semibold text-blue-700 hover:text-blue-900 sm:text-sm"
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
                <div class="col-span-full py-12 text-center">

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
        class="bs-catalog-invitation"
    >

        <div class="max-w-xl">

            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                Equipa tu espacio
            </p>

            <h2
                id="tituloEquipamiento"
                class="mt-3 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
            >
                El siguiente paso para tu PC
            </h2>

            <p class="mt-3 text-sm leading-7 text-slate-500 sm:text-base">
                Encuentra componentes y periféricos para trabajar,
                estudiar y disfrutar de tus juegos.
            </p>

        </div>

        {{-- url() genera la dirección del catálogo público. --}}
        <a
            href="{{ url('/productos') }}"
            class="bs-text-link inline-flex min-h-12 shrink-0 items-center gap-4 text-sm font-semibold text-blue-700 hover:text-blue-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600"
        >
            Ver catálogo completo
            <span aria-hidden="true" class="ml-2">→</span>
        </a>

    </section>


{{-- ======================================================
    ACOMPAÑAMIENTO AL CLIENTE
    PC: información a la izquierda e imagen a la derecha.
    Celular: información arriba e imagen debajo.
====================================================== --}}
<section
    id="acompanamiento"
    aria-labelledby="tituloAcompanamiento"
    class="bs-support-section"
>
    {{-- Dos columnas en todos los tamaños:
     texto a la izquierda e imagen a la derecha. --}}
<div class="bs-ayuda-columnas">

    {{-- COLUMNA IZQUIERDA: título y lista de ayuda. --}}
    <div class="min-w-0">

        <h2
            id="tituloAcompanamiento"
            class="text-base font-bold leading-tight tracking-tight
                   text-slate-900 sm:text-xl lg:text-2xl"
        >
            ¿Cómo te ayudamos después de tu compra?
        </h2>

        {{-- Línea decorativa debajo del título. --}}
        <div
            aria-hidden="true"
            class="mb-4 mt-3 h-1 w-12 rounded-full bg-blue-600 sm:w-16"
        ></div>
            {{-- Textos de los cuatro puntos.
                 Puedes editarlos aquí sin repetir el HTML. --}}
            @php
                $ayudas = [
                    [
                        'titulo' => 'Orientación para instalar',
                        'descripcion' => 'Te explicamos cómo colocar tu nuevo componente y qué conexiones necesita según tu equipo.',
                    ],
                    [
                        'titulo' => 'Instalación de drivers',
                        'descripcion' => 'Te indicamos dónde descargar los controladores oficiales y cómo instalarlos para tu modelo.',
                    ],
                    [
                        'titulo' => 'Configuración inicial',
                        'descripcion' => 'Te orientamos sobre los ajustes necesarios para comenzar a utilizar tu componente.',
                    ],
                    [
                        'titulo' => 'Resolución de dudas',
                        'descripcion' => 'Si algún paso no queda claro, puedes consultarnos. Te ayudamos a entender cómo continuar.',
                    ],
                ];
            @endphp

            <ul class="space-y-5 sm:space-y-6">
                @foreach ($ayudas as $ayuda)

                    <li class="flex items-start gap-3 sm:gap-4">

                        {{-- Marca de verificación. --}}
                        <span
                            aria-hidden="true"
                            class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-blue-600"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path
                                    d="m5 12 4 4L19 6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>

                        {{-- Título y explicación del punto. --}}
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold
                                       text-slate-800 sm:text-base">
                                {{ $ayuda['titulo'] }}
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                {{ $ayuda['descripcion'] }}
                            </p>
                        </div>

                    </li>

                @endforeach
            </ul>
        </div>

        {{-- COLUMNA DERECHA: ilustración o fotografía.
             object-contain permite mostrar la imagen completa. --}}
        <div class="flex min-w-0 items-center justify-center">
            <img
                src="{{ asset('img/asesoria-bytestore.webp') }}"
                alt="Asesoría para instalar y configurar componentes de PC"
                loading="lazy"
                decoding="async"
                class="h-auto max-h-[460px] w-full max-w-[520px]
                       object-contain"
            >
        </div>

    </div>
</section>
<style>
    /* Celulares: el texto recibe más espacio que la imagen. */
    .bs-ayuda-columnas {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
        align-items: center;
        gap: 12px;
    }

    /* Permite que ambas columnas se reduzcan sin desbordarse. */
    .bs-ayuda-columnas > * {
        min-width: 0;
    }

    /* Imagen completa, proporcional y dentro de su columna. */
    .bs-ayuda-columnas img {
        display: block;
        width: 100%;
        max-width: 100%;
        height: auto;
        margin-inline: auto;
        object-fit: contain;
    }

    /* Ajustes para que la lista sea más compacta en celular. */
    @media (max-width: 639px) {
        .bs-ayuda-columnas h3 {
            font-size: 13px;
            line-height: 1.4;
        }

        .bs-ayuda-columnas p {
            font-size: 12px;
            line-height: 1.55;
            overflow-wrap: break-word;
        }
    }

    /* Tablets y computadoras: columnas del mismo ancho. */
    @media (min-width: 768px) {
        .bs-ayuda-columnas {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 32px;
        }
    }

    @media (min-width: 1024px) {
        .bs-ayuda-columnas {
            gap: 48px;
        }
    }
</style>
@endsection
{{-- Fin del contenido. El layout se encarga del carrito y del footer. --}}
