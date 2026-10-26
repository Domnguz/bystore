{{-- Navegación principal y cuenta, con ubicación compartida. --}}
<header
    id="navbarPrincipal"
    class="bs-navbar {{ request()->is('/') ? 'fixed invisible -translate-y-full opacity-0 pointer-events-none' : 'sticky' }} inset-x-0 top-0 z-40 transition-all duration-300 motion-reduce:transition-none"
>
    <nav aria-label="Navegación principal" class="bs-nav-container mx-auto max-w-7xl px-4 sm:px-6">
        <div class="bs-nav-bar">
            <a href="{{ url('/') }}" class="bs-nav-brand" aria-label="ByteStore, Tu PC al siguiente nivel. Ir al inicio">
                <span class="bs-nav-brand-name">Byte<span>Store</span><span class="bs-brand-dot" aria-hidden="true"></span></span>
                <span class="bs-nav-tagline">Tu PC, al siguiente nivel.</span>
            </a>
            <button id="menuToggle" type="button" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú" class="bs-menu-toggle">
                <span class="bs-menu-lines" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>
            <div id="menuPrincipal" class="bs-nav-menu">
                <p class="bs-nav-mobile-heading">Explora ByteStore</p>
                <div class="bs-nav-links">
                    <a href="{{ url('/') }}" class="bs-nav-link {{ request()->routeIs('inicio') ? 'is-active' : '' }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>
                        @include('partials.nav-icon', ['icon' => 'home'])<span>Inicio</span>
                    </a>
                    <a href="{{ url('/productos') }}" class="bs-nav-link {{ request()->is('productos*') ? 'is-active' : '' }}" @if(request()->routeIs('catalogo')) aria-current="page" @endif>
                        @include('partials.nav-icon', ['icon' => 'box'])<span>Productos</span>
                    </a>
                    <details id="categoriasDropdown" class="bs-nav-dropdown bs-nav-categories {{ request()->is('categoria/*') ? 'is-active' : '' }}">
                        <summary class="bs-nav-link">
                            @include('partials.nav-icon', ['icon' => 'grid'])<span>Categorías</span>
                            <span class="bs-nav-chevron">@include('partials.nav-icon', ['icon' => 'chevron'])</span>
                        </summary>
                        <div class="bs-nav-dropdown-panel bs-category-panel">
                            <p class="bs-dropdown-label">Encuentra tu componente</p>
                            <div class="bs-category-links">
                                @forelse($categorias as $categoria)
                                    <a href="{{ url('/categoria/'.$categoria->id) }}" class="bs-category-link {{ request()->is('categoria/'.$categoria->id) ? 'is-active' : '' }}" @if(request()->is('categoria/'.$categoria->id)) aria-current="page" @endif>
                                        <span class="bs-category-marker" aria-hidden="true"></span>{{ $categoria->nombre }}
                                    </a>
                                @empty
                                    <p class="bs-nav-empty">Aún no hay categorías.</p>
                                @endforelse
                            </div>
                            <a href="{{ url('/productos') }}" class="bs-category-all">Ver todos los productos <span aria-hidden="true">→</span></a>
                        </div>
                    </details>
                </div>
                <div class="bs-nav-account">
                    @auth
                        <details id="cuentaDropdown" class="bs-nav-dropdown bs-account-dropdown">
                            <summary class="bs-account-toggle">
                                <span class="bs-account-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                                <span class="bs-account-text"><span>Mi cuenta</span><strong>{{ auth()->user()->name }}</strong></span>
                                <span class="bs-nav-chevron">@include('partials.nav-icon', ['icon' => 'chevron'])</span>
                            </summary>
                            <div class="bs-nav-dropdown-panel bs-account-panel">
                                <p class="bs-dropdown-label">Tu espacio en ByteStore</p>
                                <a href="{{ route('profile') }}" class="bs-account-link {{ request()->is('profile*') ? 'is-active' : '' }}" @if(request()->is('profile')) aria-current="page" @endif>
                                    @include('partials.nav-icon', ['icon' => 'user'])<span>Mi perfil</span>
                                </a>
                                <a href="{{ route('mis-pedidos.index') }}" class="bs-account-link {{ request()->is('mis-pedidos*') ? 'is-active' : '' }}" @if(request()->routeIs('mis-pedidos.index')) aria-current="page" @endif>
                                    @include('partials.nav-icon', ['icon' => 'orders'])<span>Mis pedidos</span>
                                </a>
                                @if(auth()->user()->rol === 'admin')
                                    <a href="{{ url('/admin') }}" class="bs-account-link bs-admin-link">
                                        @include('partials.nav-icon', ['icon' => 'admin'])<span>Administración</span>
                                    </a>
                                @endif
                                <form action="{{ url('/logout') }}" method="POST" class="bs-logout-form">
                                    @csrf
                                    <button type="submit" class="bs-account-link bs-logout-button">@include('partials.nav-icon', ['icon' => 'logout'])<span>Cerrar sesión</span></button>
                                </form>
                            </div>
                        </details>
                    @else
                        <a href="{{ url('/login') }}" class="bs-nav-login">@include('partials.nav-icon', ['icon' => 'user'])<span>Iniciar sesión</span></a>
                    @endauth
                </div>
            </div>
            <button id="abrirCarrito" type="button" aria-label="Abrir carrito" aria-haspopup="dialog" aria-controls="carritoModal" class="bs-nav-cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l3 12h10l3-9H6" />
                    <circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                </svg>
                <span id="contadorCarrito" class="bs-nav-cart-count" aria-live="polite" aria-atomic="true">{{ collect(session('carrito', []))->sum('cantidad') }}</span>
            </button>
        </div>
    </nav>

    {{-- UBICACIÓN:
         Está dentro del mismo header para aparecer junto con el menú. --}}
    <div class="border-t border-gray-100 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">

            <details id="selectorUbicacion" class="bs-ubicacion">

                {{-- Texto visible en la franja --}}
                <summary class="bs-ubicacion__resumen">
                    <svg
                        width="19"
                        height="19"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"
                        />
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>

                    <span class="bs-ubicacion__etiqueta">
                        Tu ubicación:
                    </span>

                    <strong id="textoUbicacion">
                        Elegir ubicación
                    </strong>

                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m6 9 6 6 6-6"
                        />
                    </svg>
                </summary>

                {{-- Panel para detectar o actualizar la ubicación --}}
                <div class="bs-ubicacion__panel">
                    <h3>¿Dónde te encuentras?</h3>

                    <p id="explicacionUbicacion">
                        Con tu permiso, enviaremos tus coordenadas
                        a BigDataCloud para identificar tu ciudad y país.
                    </p>

                    <button
                        id="detectarUbicacion"
                        type="button"
                        aria-describedby="explicacionUbicacion"
                    >
                        Usar mi ubicación
                    </button>

                    <p
                        id="estadoUbicacion"
                        class="bs-ubicacion__estado"
                        role="status"
                        aria-live="polite"
                        aria-atomic="true"
                    ></p>
                </div>

            </details>

        </div>
    </div>

</header>

<div id="menuBackdrop" class="bs-menu-backdrop" aria-hidden="true"></div>

{{-- ESTILOS DEL SELECTOR DE UBICACIÓN --}}
<style>
    .bs-ubicacion {
        position: relative;
        width: fit-content;
        max-width: 100%;
    }

    .bs-ubicacion__resumen {
        display: flex;
        align-items: center;
        gap: 7px;
        min-height: 40px;
        padding: 6px 0;
        list-style: none;
        cursor: pointer;
        color: #475569;
        font-size: 12px;
    }

    .bs-ubicacion__resumen::-webkit-details-marker {
        display: none;
    }

    .bs-ubicacion__resumen svg {
        flex-shrink: 0;
        color: var(--color-blue-600);
    }

    .bs-ubicacion__etiqueta {
        flex-shrink: 0;
    }

    .bs-ubicacion__resumen strong {
        min-width: 0;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 600;
        color: #0f172a;
    }

    .bs-ubicacion__panel {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 50;
        width: min(320px, calc(100vw - 32px));
        max-height: 60vh;
        overflow-y: auto;
        box-sizing: border-box;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 30px rgb(15 23 42 / 14%);
    }

    .bs-ubicacion__panel h3 {
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .bs-ubicacion__panel p {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
        color: #64748b;
    }

    .bs-ubicacion__panel button {
        width: 100%;
        min-height: 44px;
        margin-top: 14px;
        padding: 10px 14px;
        border: 0;
        border-radius: 9px;
        background: var(--color-blue-600);
        color: #fff;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .bs-ubicacion__panel button:hover {
        background: var(--color-blue-700);
    }

    .bs-ubicacion__panel button:disabled {
        opacity: .65;
        cursor: wait;
    }

    .bs-ubicacion__panel .bs-ubicacion__estado {
        margin-top: 10px;
    }

    .bs-ubicacion__estado:empty {
        display: none;
    }

    .bs-ubicacion__estado[data-error="true"] {
        color: #b91c1c;
    }

    .bs-ubicacion :focus-visible {
        outline: 2px solid var(--color-blue-600);
        outline-offset: 3px;
    }

    @media (max-width: 639px) {
        .bs-ubicacion__resumen {
            font-size: 11px;
        }

        .bs-ubicacion__resumen strong {
            max-width: 170px;
        }
    }
</style>
