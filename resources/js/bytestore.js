function iniciarByteStore() {
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.getElementById('menuPrincipal');
    const categorias = document.getElementById('categoriasDropdown');

    const carrito = document.getElementById('carritoModal');
    const abrir = document.getElementById('abrirCarrito');
    const cerrar = document.getElementById('cerrarCarrito');
    const contenido = document.getElementById('contenidoCarrito');
    const contador = document.getElementById('contadorCarrito');
    const aviso = document.getElementById('avisoTienda');

    // MENÚ PRINCIPAL

    function cerrarMenu() {
        menu?.classList.add('hidden');

        menuToggle?.setAttribute('aria-expanded', 'false');
        menuToggle?.setAttribute('aria-label', 'Abrir menú');

        if (categorias) {
            categorias.open = false;
        }
    }

    if (menuToggle && menu) {
        menuToggle.addEventListener('click', () => {
            const abierto =
                menuToggle.getAttribute('aria-expanded') !== 'true';

            menu.classList.toggle('hidden', !abierto);

            menuToggle.setAttribute(
                'aria-expanded',
                String(abierto)
            );

            menuToggle.setAttribute(
                'aria-label',
                abierto ? 'Cerrar menú' : 'Abrir menú'
            );
        });

        document.addEventListener('click', (event) => {
            const dentroDelMenu = menu.contains(event.target);
            const dentroDelBoton = menuToggle.contains(event.target);

            if (!dentroDelMenu && !dentroDelBoton) {
                cerrarMenu();
            }

            if (categorias && !categorias.contains(event.target)) {
                categorias.open = false;
            }
        });

        menu.querySelectorAll('a').forEach((enlace) => {
            enlace.addEventListener('click', cerrarMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            const estabaAbierto =
                menuToggle.getAttribute('aria-expanded') === 'true';

            cerrarMenu();

            if (estabaAbierto && !carrito?.open) {
                menuToggle.focus();
            }
        });

        window.matchMedia('(min-width: 1024px)')
            .addEventListener('change', cerrarMenu);
    }

    // CARRITO LATERAL

    if (!carrito || !abrir || !cerrar || !contenido) return;

    let overflowAnterior = '';
    let focoAnterior = null;

    function abrirCarrito() {
        cerrarMenu();

        if (carrito.open) return;

        focoAnterior = document.activeElement;
        overflowAnterior = document.body.style.overflow;

        carrito.showModal();
        document.body.style.overflow = 'hidden';
    }

    abrir.addEventListener('click', abrirCarrito);

    cerrar.addEventListener('click', () => {
        carrito.close();
    });

    // Cierra al pulsar el fondo situado fuera del panel.
    carrito.addEventListener('click', (event) => {
        if (event.target !== carrito) return;

        const caja = carrito.getBoundingClientRect();

        const fuera =
            event.clientX < caja.left ||
            event.clientX > caja.right ||
            event.clientY < caja.top ||
            event.clientY > caja.bottom;

        if (fuera) {
            carrito.close();
        }
    });

    // Escape también cierra el diálogo de forma nativa.
    carrito.addEventListener('close', () => {
        document.body.style.overflow = overflowAnterior;

        if (
            focoAnterior instanceof HTMLElement &&
            focoAnterior.isConnected &&
            !focoAnterior.closest('[inert]') &&
            focoAnterior.getClientRects().length > 0 &&
            window.getComputedStyle(focoAnterior).visibility !== 'hidden'
        ) {
            focoAnterior.focus();
        }
    });

    // AVISOS

    let temporizadorAviso;

    function mostrarAviso(mensaje) {
        if (!aviso) {
            window.alert(mensaje);
            return;
        }

        window.clearTimeout(temporizadorAviso);

        aviso.textContent = mensaje;
        aviso.classList.remove('hidden');

        temporizadorAviso = window.setTimeout(() => {
            aviso.classList.add('hidden');
        }, 7000);
    }

    // AGREGAR PRODUCTOS SIN RECARGAR LA PÁGINA

    document.querySelectorAll('.form-agregar-carrito').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (form.dataset.enviando === 'true') return;

            form.dataset.enviando = 'true';

            const boton = form.querySelector(
                'button[type="submit"], button:not([type])'
            );

            const estadoAnterior = boton?.disabled;

            if (boton) {
                boton.disabled = true;
            }

            let agregado = false;

            try {
                const id = form.dataset.id;

                const token =
                    document.querySelector('meta[name="csrf-token"]')?.content ||
                    form.querySelector('[name="_token"]')?.value;

                if (!id || !token) {
                    throw new Error(
                        'No se pudo preparar la compra. Actualiza la página.'
                    );
                }

                const response = await fetch(
                    `/carrito/agregar/${encodeURIComponent(id)}`,
                    {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                        },
                        body: new FormData(form),
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'No se pudo agregar el producto. Actualiza la página e inténtalo nuevamente.'
                    );
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(
                        data.message || 'No se pudo agregar el producto.'
                    );
                }

                agregado = true;

                if (contador && data.cantidad !== undefined) {
                    contador.textContent = data.cantidad;
                }

                const panel = await fetch('/carrito/contenido', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (!panel.ok) {
                    throw new Error(
                        'No se pudo actualizar el panel del carrito.'
                    );
                }

                contenido.innerHTML = await panel.text();

                mostrarAviso('Producto agregado al carrito.');
                abrirCarrito();
            } catch (error) {
                mostrarAviso(
                    agregado
                        ? 'El producto se agregó, pero no se pudo actualizar el panel. Recarga la página para verlo.'
                        : error instanceof Error
                            ? error.message
                            : 'No se pudo conectar. Inténtalo nuevamente.'
                );
            } finally {
                delete form.dataset.enviando;

                if (boton) {
                    boton.disabled = estadoAnterior;
                }
            }
        });
    });
}


// VISIBILIDAD DEL NAVBAR Y DEL BOTÓN DEL CARRITO

function configurarVisibilidadPortada() {
    const portada = document.getElementById('inicio');
    const navbar = document.getElementById('navbarPrincipal');
    const botonCarrito = document.getElementById('abrirCarrito');

    function mostrarElemento(elemento, visible, desplazamiento) {
        if (!elemento) return;

        elemento.classList.toggle('invisible', !visible);
        elemento.classList.toggle('opacity-0', !visible);
        elemento.classList.toggle('pointer-events-none', !visible);
        elemento.classList.toggle(desplazamiento, !visible);

        elemento.inert = !visible;

        if (visible) {
            elemento.removeAttribute('aria-hidden');
        } else {
            elemento.setAttribute('aria-hidden', 'true');
        }
    }

    let estadoAnterior = null;

    function actualizar() {
        // Sin portada, los controles siempre están visibles.
        // En el inicio aparecen cuando el video sale de la pantalla.
        const visible =
            !portada || portada.getBoundingClientRect().bottom <= 1;

        if (visible === estadoAnterior) return;

        estadoAnterior = visible;

        mostrarElemento(
            navbar,
            visible,
            '-translate-y-full'
        );

        mostrarElemento(
            botonCarrito,
            visible,
            'translate-y-4'
        );

        if (!visible) {
            const menu = document.getElementById('menuPrincipal');
            const toggle = document.getElementById('menuToggle');
            const categorias = document.getElementById('categoriasDropdown');

            menu?.classList.add('hidden');

            toggle?.setAttribute('aria-expanded', 'false');
            toggle?.setAttribute('aria-label', 'Abrir menú');

            if (categorias) {
                categorias.open = false;
            }
        }
    }

    let pendiente = false;

    function programarActualizacion() {
        if (pendiente) return;

        pendiente = true;

        window.requestAnimationFrame(() => {
            pendiente = false;
            actualizar();
        });
    }

    window.addEventListener('scroll', programarActualizacion, {
        passive: true,
    });

    window.addEventListener('resize', programarActualizacion);
    window.addEventListener('pageshow', programarActualizacion);

    actualizar();
}


// INICIALIZACIÓN: CADA FUNCIÓN SE EJECUTA UNA SOLA VEZ

function iniciarPagina() {
    configurarVisibilidadPortada();
    iniciarByteStore();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarPagina, {
        once: true,
    });
} else {
    iniciarPagina();
}