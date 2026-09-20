<header
    id="navbarPrincipal"
    class="{{ request()->is('/') ? 'fixed invisible -translate-y-full opacity-0 pointer-events-none' : 'sticky' }} inset-x-0 top-0 z-40 border-b border-gray-200 bg-white transition-all duration-300 motion-reduce:transition-none"
>
    <nav aria-label="Navegación principal" class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex min-h-16 items-center justify-between gap-4">

            <a href="{{ url('/') }}" class="shrink-0 text-xl font-extrabold tracking-tight text-slate-950">
                Byte<span class="text-blue-600">Store</span>
            </a>

            <button
                id="menuToggle"
                type="button"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Abrir menú"
                class="flex h-11 w-11 items-center justify-center rounded-lg border border-gray-200 text-slate-800 lg:hidden"
            >
                <svg width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Un mismo menú para computadora y celular --}}
            <div
                id="menuPrincipal"
                class="hidden absolute inset-x-0 top-full max-h-[calc(100dvh-4rem)] overflow-y-auto border-b border-gray-200 bg-white p-4 shadow-lg lg:static lg:block lg:max-h-none lg:overflow-visible lg:border-0 lg:p-0 lg:shadow-none"
            >
                <div class="flex min-w-0 flex-col gap-2 lg:flex-row lg:items-center lg:gap-3">
                    <a href="{{ url('/') }}"
                       class="rounded-lg px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                        Inicio
                    </a>

                    <a href="{{ url('/productos') }}"
                       class="rounded-lg px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                        Productos
                    </a>

                    <details id="categoriasDropdown" class="relative">
                        <summary class="cursor-pointer rounded-lg px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50">
                            Categorías
                        </summary>

                        <div class="mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 lg:absolute lg:right-0 lg:w-64 lg:shadow-xl">
                            @forelse($categorias as $categoria)
                                <a
                                    href="{{ url('/categoria/'.$categoria->id) }}"
                                    class="block break-words rounded-lg px-4 py-3 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700"
                                >
                                    {{ $categoria->nombre }}
                                </a>
                            @empty
                                <p class="px-4 py-3 text-sm text-gray-500">
                                    Aún no hay categorías.
                                </p>
                            @endforelse
                        </div>
                    </details>

                    @auth
                        <a
                            href="{{ route('profile') }}"
                            class="max-w-full truncate rounded-lg bg-gray-100 px-4 py-3 text-sm font-semibold text-slate-800 lg:max-w-48"
                        >
                            {{ auth()->user()->name }}
                        </a>

                        @if(auth()->user()->rol === 'admin')
                            <a href="{{ url('/admin') }}"
                               class="rounded-lg px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                                Administración
                            </a>
                        @endif

                        <form action="{{ url('/logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="w-full rounded-lg px-4 py-3 text-left text-sm font-semibold text-slate-600 hover:bg-gray-100"
                            >
                                Cerrar sesión
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ url('/login') }}"
                            class="rounded-lg bg-blue-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Iniciar sesión
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>