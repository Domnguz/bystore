<!DOCTYPE html>
<html lang="es" class="bs-storefront">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ByteStore | Componentes para PC')</title>
    <meta name="description" content="@yield('description', 'Componentes para PC y servicio técnico en ByteStore. Consulta productos, disponibilidad y opciones de entrega.')">
    @if(request()->is('admin*', 'login', 'registro', 'verificar-correo*', 'olvide-contrasena', 'restablecer-contrasena*', 'checkout', 'carrito*', 'profile*', 'mis-pedidos*', 'reclamaciones*'))
        <meta name="robots" content="noindex, nofollow">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
    <meta property="og:title" content="@yield('title', 'ByteStore | Componentes para PC')">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('img/logo-bytestore.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>

<body id="inicio-pagina" class="@yield('body-class') flex min-h-screen flex-col bg-slate-50 text-slate-900">

    @php
        $categorias = $categorias ?? \App\Models\Categoria::all();
    @endphp

    @include('partials.navbar')

    <main class="w-full min-w-0 flex-grow">
        {{-- Portada a todo el ancho --}}
        @yield('banner')

        {{-- Resto del contenido --}}
        <div class="bs-page-content mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
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

            <div id="contenidoCarrito" data-url="{{ url('/carrito/contenido') }}" class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-5">
                @include('partials.carrito-contenido')
            </div>
        </div>
    </dialog>

    {{-- Footer solo en las páginas de tienda e información al cliente. --}}
    @if(request()->is('/', 'productos', 'productos/*', 'categoria/*', 'carrito', 'contacto', 'privacidad', 'reclamaciones', 'reclamaciones/*'))
        @include('partials.footer')
    @endif

</body>
</html>
