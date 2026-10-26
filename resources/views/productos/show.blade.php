{{-- Usa el navbar, carrito y footer de la tienda. --}}
@extends('layouts.app')

@section('title', $producto->nombre . ' | ByteStore')
@section('description', \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $producto->descripcion ?: $producto->nombre), 155))

@section('contenido')

    {{-- Reúne la foto principal y las adicionales.
         Elimina valores vacíos y fotografías duplicadas. --}}
    @php
        $fotos = collect([$producto->imagen])
            ->merge($producto->imagenes->pluck('ruta'))
            ->filter()
            ->unique()
            ->values();
    @endphp

    {{-- Enlaces para regresar al inicio o al catálogo. --}}
    <nav aria-label="Ruta de navegación" class="mb-8">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <li>
                <a href="{{ url('/') }}" class="hover:text-blue-600">
                    Inicio
                </a>
            </li>

            <li aria-hidden="true">/</li>

            <li>
                <a href="{{ url('/productos') }}" class="hover:text-blue-600">
                    Productos
                </a>
            </li>

            <li aria-hidden="true">/</li>

            <li aria-current="page" class="break-words text-slate-900">
                {{ $producto->nombre }}
            </li>
        </ol>
    </nav>

    {{-- En celular: galería arriba e información debajo.
         En PC: galería a la izquierda e información a la derecha. --}}
    <div class="grid items-start gap-8 lg:grid-cols-2 lg:gap-12">

        {{-- GALERÍA DEL PRODUCTO --}}
        <section aria-label="Fotografías del producto" class="min-w-0">

            @if ($fotos->isNotEmpty())
                <div class="flex flex-col gap-3 xl:flex-row">

                    {{-- Fotografía grande.
                         object-contain muestra toda la imagen sin recortarla. --}}
                    <div class="min-w-0 flex-1 xl:order-2">
                        <div class="relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-white">

                            <img
                                id="fotoPrincipalProducto"
                                src="{{ asset('storage/' . $fotos->first()) }}"
                                alt="{{ $producto->nombre }} — foto 1"
                                fetchpriority="high"
                                class="absolute inset-0 h-full w-full object-contain p-4"
                            >

                            {{-- Las flechas aparecen si hay varias fotos. --}}
                            @if ($fotos->count() > 1)
                                <button
                                    id="fotoAnterior"
                                    type="button"
                                    aria-label="Ver fotografía anterior"
                                    class="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-800 shadow-sm hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-blue-600"
                                >
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m15 6-6 6 6 6"
                                        />
                                    </svg>
                                </button>

                                <button
                                    id="fotoSiguiente"
                                    type="button"
                                    aria-label="Ver fotografía siguiente"
                                    class="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-800 shadow-sm hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-blue-600"
                                >
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m9 6 6 6-6 6"
                                        />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Indica qué fotografía está seleccionada. --}}
                        <p
                            id="contadorFotos"
                            aria-live="polite"
                            class="mt-3 text-center text-xs text-slate-500"
                        >
                            Foto 1 de {{ $fotos->count() }}
                        </p>
                    </div>

                    {{-- Miniaturas: horizontales en celular,
                         verticales en pantallas grandes. --}}
                    <div
                        aria-label="Seleccionar fotografía"
                        class="flex gap-2 overflow-x-auto p-1 xl:order-1 xl:max-h-[560px] xl:w-20 xl:shrink-0 xl:flex-col xl:overflow-y-auto"
                    >
                        @foreach ($fotos as $foto)
                            <button
                                type="button"
                                data-miniatura
                                data-src="{{ asset('storage/' . $foto) }}"
                                data-alt="{{ $producto->nombre }} — foto {{ $loop->iteration }}"
                                aria-label="Ver fotografía {{ $loop->iteration }}"
                                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white p-1 aria-pressed:border-blue-600 aria-pressed:ring-1 aria-pressed:ring-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            >
                                <img
                                    src="{{ asset('storage/' . $foto) }}"
                                    alt=""
                                    loading="lazy"
                                    class="h-full w-full object-contain"
                                >
                            </button>
                        @endforeach
                    </div>

                </div>
            @else
                {{-- Se muestra cuando el producto no tiene fotos. --}}
                <div class="flex aspect-square items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400">
                    Imagen no disponible
                </div>
            @endif

        </section>

        {{-- INFORMACIÓN DEL PRODUCTO --}}
        <section class="min-w-0">

            {{-- Categoría registrada desde el administrador. --}}
            <p class="text-xs font-semibold uppercase tracking-widest text-blue-600">
                {{ $producto->categoria?->nombre ?? 'Componentes' }}
            </p>

            {{-- Nombre completo del producto. --}}
            @if($producto->marca || $producto->modelo)<p class="mt-3 text-sm text-slate-600">{{ $producto->marca }} {{ $producto->modelo }}</p>@endif
            @if($producto->sku)<p class="mt-2 text-xs text-slate-500">SKU: {{ $producto->sku }}</p>@endif
            <h1 class="mt-3 break-words text-3xl font-bold leading-tight tracking-tight text-slate-950 sm:text-4xl">
                {{ $producto->nombre }}
            </h1>
            <div class="mt-3 flex items-center gap-2" aria-label="Calificación de {{ number_format((float) $producto->resenas_avg_calificacion, 1) }} sobre 5, {{ $producto->resenas_count }} opiniones">
                <span class="text-xl tracking-wide text-amber-500" aria-hidden="true">{{ str_repeat('★', (int) round((float) $producto->resenas_avg_calificacion)) }}{{ str_repeat('☆', 5 - (int) round((float) $producto->resenas_avg_calificacion)) }}</span>
                <span class="text-sm text-slate-600">{{ $producto->resenas_count ? number_format((float) $producto->resenas_avg_calificacion, 1).' / 5 · '.$producto->resenas_count.' opiniones' : 'Aún sin calificaciones' }}</span>
            </div>

            <p class="mt-4 text-sm text-slate-500">
                Vendido por
                <span class="font-semibold text-slate-800">
                    ByteStore
                </span>
            </p>

            {{-- Precio y stock obtenidos de la base de datos. --}}
            <div class="mt-6 border-y border-slate-200 py-6">
                <p class="text-sm text-slate-500">
                    Precio final (IGV incluido)
                </p>

                <p class="mt-2 text-4xl font-bold tracking-tight text-blue-600">
                    S/ {{ number_format($producto->precio, 2) }}
                </p>

                <p class="mt-4 text-sm font-medium text-slate-700">
                    @if ($producto->stock > 0)
                        Disponible · {{ $producto->stock }} unidades
                    @else
                        Producto agotado
                    @endif
                </p>
            </div>

            {{-- Conserva los saltos de línea de la descripción. --}}
            <div class="py-6">
                <h2 class="text-lg font-semibold text-slate-950">
                    Descripción del producto
                </h2>

                <p class="mt-3 whitespace-pre-line break-words leading-7 text-slate-600">{{ $producto->descripcion ?: 'Este producto todavía no tiene una descripción.' }}</p>
            </div>

            @foreach(['especificaciones' => 'Especificaciones técnicas', 'compatibilidad' => 'Compatibilidad', 'garantia' => 'Garantía'] as $campo => $titulo)
                @if($producto->{$campo})
                    <section class="mb-5 border-t border-slate-200 pt-5"><h2 class="font-bold">{{ $titulo }}</h2><p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $producto->{$campo} }}</p></section>
                @endif
            @endforeach

            {{-- Usa la ruta y la clase del carrito existente.
                 El JavaScript de la tienda procesa este formulario. --}}
            <form
                action="{{ url('/carrito/agregar/' . $producto->id) }}"
                method="POST"
                class="form-agregar-carrito"
                data-id="{{ $producto->id }}"
            >
                @csrf

                <button
                    type="submit"
                    @disabled($producto->stock <= 0)
                    class="min-h-14 w-full rounded-lg bg-blue-600 px-6 py-4 font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500"
                >
                    {{ $producto->stock > 0 ? 'Agregar al carrito' : 'Sin stock disponible' }}
                </button>
            </form>

            @if ($producto->stock > 0)
                <p class="mt-3 text-sm text-slate-500">
                    Se agregará una unidad al carrito.
                </p>
            @endif

            <a
                href="{{ url('/productos') }}"
                class="mt-6 inline-flex min-h-11 items-center text-sm font-medium text-slate-600 hover:text-blue-600"
            >
                ← Volver al catálogo
            </a>

        </section>
    </div>

    {{-- INTERACCIÓN DE LA GALERÍA:
         cambia la foto al pulsar miniaturas o flechas. --}}
    <section class="mt-10 rounded-2xl border border-slate-200 bg-white p-5 sm:p-7" aria-labelledby="resenas-producto">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h2 id="resenas-producto" class="text-2xl font-bold">Opiniones de compradores</h2>
                <p class="mt-1 text-sm text-slate-600">Solo pueden calificar quienes recibieron este producto en un pedido.</p>
            </div>
            @if($producto->resenas_count)
                <p class="text-lg font-semibold text-amber-600">★ {{ number_format((float) $producto->resenas_avg_calificacion, 1) }} <span class="text-sm font-normal text-slate-500">({{ $producto->resenas_count }})</span></p>
            @endif
        </div>
        @forelse($producto->resenas as $resena)
            <article class="border-b border-slate-100 py-4 last:border-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="tracking-wide text-amber-500" aria-label="{{ $resena->calificacion }} de 5 estrellas">{{ str_repeat('★', $resena->calificacion) }}{{ str_repeat('☆', 5 - $resena->calificacion) }}</span>
                    <span class="text-xs font-medium text-green-700">Compra verificada</span>
                    <time class="text-xs text-slate-500">{{ $resena->created_at->timezone('America/Lima')->format('d/m/Y') }}</time>
                </div>
                @if($resena->comentario)<p class="mt-2 whitespace-pre-line text-slate-700">{{ $resena->comentario }}</p>@endif
            </article>
        @empty
            <p class="py-5 text-slate-600">Todavía no hay opiniones para este producto.</p>
        @endforelse
    </section>

    <script>
        (() => {
            const imagen = document.getElementById('fotoPrincipalProducto');
            const miniaturas = [
                ...document.querySelectorAll('[data-miniatura]')
            ];
            const contador = document.getElementById('contadorFotos');

            // No ejecuta la galería si no existen fotografías.
            if (!imagen || !miniaturas.length) return;

            let indiceActual = 0;

            function mostrarFoto(indice) {
                // Al llegar al final, vuelve a la primera fotografía.
                indiceActual = (
                    indice + miniaturas.length
                ) % miniaturas.length;

                const seleccionada = miniaturas[indiceActual];

                imagen.src = seleccionada.dataset.src;
                imagen.alt = seleccionada.dataset.alt;

                // Resalta la miniatura seleccionada.
                miniaturas.forEach((boton, posicion) => {
                    boton.setAttribute(
                        'aria-pressed',
                        String(posicion === indiceActual)
                    );
                });

                contador.textContent =
                    `Foto ${indiceActual + 1} de ${miniaturas.length}`;
            }

            // Selección directa mediante miniaturas.
            miniaturas.forEach((boton, indice) => {
                boton.addEventListener('click', () => {
                    mostrarFoto(indice);
                });
            });

            // Navegación mediante las flechas.
            document.getElementById('fotoAnterior')
                ?.addEventListener('click', () => {
                    mostrarFoto(indiceActual - 1);
                });

            document.getElementById('fotoSiguiente')
                ?.addEventListener('click', () => {
                    mostrarFoto(indiceActual + 1);
                });
        })();
    </script>

@endsection
