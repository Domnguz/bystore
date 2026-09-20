<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ByteStore | Componentes para PC')</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>

<body class="flex min-h-screen flex-col bg-gray-100 text-slate-900">

    @php
        $categorias = $categorias ?? \App\Models\Categoria::all();
    @endphp

    @include('partials.navbar')

    <main class="w-full min-w-0 flex-grow">
        {{-- Portada a todo el ancho --}}
        @yield('banner')

        {{-- Resto del contenido --}}
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
            @if(session('success'))
                <div role="status" class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('contenido')
        </div>
    </main>

    {{-- Avisos del carrito --}}
    <div
        id="avisoTienda"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="fixed bottom-24 left-4 right-4 z-30 hidden rounded-xl border border-blue-200 bg-white p-4 text-sm text-slate-800 shadow-lg sm:left-6 sm:right-auto sm:max-w-sm"
    ></div>

    {{-- Botón del carrito --}}
    <button
    id="abrirCarrito"
    type="button"
    aria-label="Abrir carrito"
    aria-haspopup="dialog"
    aria-controls="carritoModal"
    class="fixed bottom-4 right-4 z-30 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl transition-all duration-300 hover:bg-blue-700 sm:bottom-6 sm:right-6"
>
    <svg
        width="25"
        height="25"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3 3h2l3 12h10l3-9H6"
        />
        <circle cx="9" cy="20" r="1"/>
        <circle cx="18" cy="20" r="1"/>
    </svg>

    <span
        id="contadorCarrito"
        class="absolute -right-1 -top-2 flex h-6 min-w-6 items-center justify-center rounded-full border-2 border-white bg-slate-900 px-1 text-xs font-bold text-white"
    >
        {{ collect(session('carrito', []))->sum('cantidad') }}
    </span>
</button>

    {{-- Carrito responsive --}}
    <dialog
        id="carritoModal"
        aria-labelledby="tituloCarrito"
        class="fixed inset-y-0 left-auto right-0 m-0 h-dvh max-h-none w-full max-w-[400px] border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-black/60"
    >
        <div class="flex h-full min-h-0 flex-col">
            <div class="flex shrink-0 items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
                <h2 id="tituloCarrito" class="text-xl font-bold">
                    Mi carrito
                </h2>

                <button
                    id="cerrarCarrito"
                    type="button"
                    autofocus
                    aria-label="Cerrar carrito"
                    class="flex h-11 w-11 items-center justify-center rounded-lg bg-gray-100 text-2xl hover:bg-gray-200"
                >
                    ×
                </button>
            </div>

            <div id="contenidoCarrito" class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-5">
                @include('partials.carrito-contenido')
            </div>
        </div>
    </dialog>

    <footer class="mt-auto border-t border-slate-800 bg-slate-950 px-5 pb-28 pt-10 text-slate-400 sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ url('/') }}" class="text-xl font-extrabold text-white">
                    Byte<span class="text-blue-400">Store</span>
                </a>

                <p class="mt-2 text-sm">
                    Componentes para PC y servicio técnico.
                </p>
            </div>

            <p class="text-sm">
                © {{ date('Y') }} ByteStore. Todos los derechos reservados.
            </p>
        </div>
    </footer>

</body>
</html>
