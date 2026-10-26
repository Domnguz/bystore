function iniciarBuscadorCatalogo() {
    document.querySelectorAll('[data-catalog-search]').forEach((buscador) => {
        const toggle = buscador.querySelector('summary');
        const campo = buscador.querySelector('input[type="search"]');
        const autocomplete = buscador.querySelector('[data-catalog-autocomplete]');
        const resultados = buscador.querySelector('[data-autocomplete-results]');
        let temporizador;
        let solicitud;

        function ocultarSugerencias() {
            resultados.hidden = true;
            campo.setAttribute('aria-expanded', 'false');
        }

        function mostrarMensaje(texto) {
            const mensaje = document.createElement('p');
            mensaje.className = 'catalog-search-empty';
            mensaje.setAttribute('role', 'status');
            mensaje.textContent = texto;
            resultados.replaceChildren(mensaje);
            resultados.hidden = false;
            campo.setAttribute('aria-expanded', 'true');
        }

        function mostrarProductos(productos, termino) {
            resultados.replaceChildren();
            if (!productos.length) {
                mostrarMensaje(`No encontramos productos para “${termino}”.`);
                return;
            }

            productos.forEach((producto) => {
                const enlace = document.createElement('a');
                enlace.className = 'catalog-search-suggestion';
                enlace.href = producto.url;
                enlace.setAttribute('role', 'option');
                enlace.setAttribute('aria-selected', 'false');

                if (producto.imagen) {
                    const imagen = document.createElement('img');
                    imagen.src = producto.imagen;
                    imagen.alt = '';
                    imagen.loading = 'lazy';
                    imagen.className = 'catalog-search-suggestion-image';
                    enlace.append(imagen);
                } else {
                    const icono = document.createElement('span');
                    icono.className = 'catalog-search-suggestion-placeholder';
                    icono.setAttribute('aria-hidden', 'true');
                    icono.textContent = '▧';
                    enlace.append(icono);
                }

                const detalle = document.createElement('span');
                detalle.className = 'catalog-search-suggestion-detail';
                const nombre = document.createElement('span');
                nombre.className = 'catalog-search-suggestion-name';
                nombre.textContent = producto.nombre;
                const categoria = document.createElement('span');
                categoria.className = 'catalog-search-suggestion-category';
                categoria.textContent = producto.categoria || 'Producto';
                detalle.append(nombre, categoria);

                const precio = document.createElement('span');
                precio.className = 'catalog-search-suggestion-price';
                precio.textContent = `S/ ${Number(producto.precio).toFixed(2)}`;
                enlace.append(detalle, precio);
                resultados.append(enlace);
            });

            resultados.hidden = false;
            campo.setAttribute('aria-expanded', 'true');
        }

        async function buscarSugerencias() {
            const termino = campo.value.trim();
            solicitud?.abort();
            if (termino.length < 2) {
                ocultarSugerencias();
                return;
            }

            solicitud = new AbortController();
            try {
                const url = new URL(autocomplete.dataset.suggestionsUrl, window.location.origin);
                url.searchParams.set('q', termino);
                const respuesta = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: solicitud.signal,
                });
                if (!respuesta.ok) throw new Error('No se pudieron cargar sugerencias.');
                mostrarProductos(await respuesta.json(), termino);
            } catch (error) {
                if (error.name !== 'AbortError') ocultarSugerencias();
            }
        }

        campo.addEventListener('input', () => {
            window.clearTimeout(temporizador);
            resultados.replaceChildren();
            ocultarSugerencias();
            temporizador = window.setTimeout(buscarSugerencias, 220);
        });
        campo.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' && !resultados.hidden) {
                event.preventDefault();
                resultados.querySelector('a')?.focus();
            } else if (event.key === 'Escape' && !resultados.hidden) {
                event.preventDefault();
                ocultarSugerencias();
                campo.focus();
            }
        });
        resultados.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
            const opciones = [...resultados.querySelectorAll('a')];
            const actual = opciones.indexOf(document.activeElement);
            const siguiente = event.key === 'ArrowDown' ? actual + 1 : actual - 1;
            if (siguiente >= 0 && siguiente < opciones.length) {
                event.preventDefault();
                opciones[siguiente].focus();
            } else if (event.key === 'ArrowUp' && actual === 0) {
                event.preventDefault();
                campo.focus();
            }
        });

        function cerrarBuscador(devolverFoco = false) {
            if (!buscador.open) return;
            solicitud?.abort();
            ocultarSugerencias();
            buscador.open = false;
            if (devolverFoco) toggle.focus();
        }

        buscador.addEventListener('toggle', (event) => {
            if (event.target !== buscador) return;
            toggle.setAttribute('aria-label', buscador.open ? 'Cerrar buscador de productos' : 'Abrir buscador de productos');
            if (buscador.open) campo.focus({ preventScroll: true });
        });

        buscador.querySelector('[data-search-close]').addEventListener('click', () => cerrarBuscador(true));
        document.addEventListener('click', (event) => {
            if (!buscador.contains(event.target)) cerrarBuscador();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && buscador.open) {
                event.preventDefault();
                cerrarBuscador(true);
            }
        });
    });
}

function iniciarByteStore() {
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.getElementById('menuPrincipal');
    const categorias = document.getElementById('categoriasDropdown');
    const cuenta = document.getElementById('cuentaDropdown');
    const fondoMenu = document.getElementById('menuBackdrop');
    const escritorio = window.matchMedia('(min-width: 1024px)');

    const carrito = document.getElementById('carritoModal');
    const abrir = document.getElementById('abrirCarrito');
    const cerrar = document.getElementById('cerrarCarrito');
    const contenido = document.getElementById('contenidoCarrito');
    const contador = document.getElementById('contadorCarrito');
    const aviso = document.getElementById('avisoTienda');

    // MENÚ PRINCIPAL

    function cerrarMenu() {
        menu?.classList.remove('is-open');
        fondoMenu?.classList.remove('is-open');
        document.body.classList.remove('bs-menu-open');
        if (menu) menu.inert = !escritorio.matches;

        menuToggle?.setAttribute('aria-expanded', 'false');
        menuToggle?.setAttribute('aria-label', 'Abrir menú');

        if (categorias) {
            categorias.open = false;
        }
        if (cuenta) cuenta.open = false;
    }

    if (menuToggle && menu) {
        cerrarMenu();
        document.addEventListener('bytestore:cerrar-menu', cerrarMenu);
        fondoMenu?.addEventListener('click', cerrarMenu);

        menuToggle.addEventListener('click', () => {
            const abierto =
                menuToggle.getAttribute('aria-expanded') !== 'true';

            if (!abierto) {
                cerrarMenu();
                return;
            }

            menu.inert = false;
            menu.classList.add('is-open');
            fondoMenu?.classList.add('is-open');
            document.body.classList.add('bs-menu-open');
            if (cuenta && !escritorio.matches) cuenta.open = true;
            const ubicacion = document.getElementById('selectorUbicacion');
            if (ubicacion) ubicacion.open = false;

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
            if (cuenta && !cuenta.contains(event.target) && escritorio.matches) {
                cuenta.open = false;
            }
        });

        menu.querySelectorAll('a').forEach((enlace) => {
            enlace.addEventListener('click', cerrarMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (event.defaultPrevented || carrito?.open) return;

            if (event.key === 'Tab' && !escritorio.matches && menu.classList.contains('is-open')) {
                const controles = [menuToggle, ...menu.querySelectorAll('a, button, summary')]
                    .filter((control) => control.getClientRects().length > 0 && !control.closest('[inert]'));
                const primero = controles[0];
                const ultimo = controles.at(-1);
                if (event.shiftKey && document.activeElement === primero) {
                    event.preventDefault();
                    ultimo?.focus();
                } else if (!event.shiftKey && document.activeElement === ultimo) {
                    event.preventDefault();
                    primero.focus();
                }
                return;
            }

            if (event.key !== 'Escape') return;

            const desplegable = document.activeElement?.closest('.bs-nav-dropdown');
            if (desplegable?.open) {
                event.preventDefault();
                desplegable.open = false;
                desplegable.querySelector('summary').focus();
                return;
            }

            const estabaAbierto =
                menuToggle.getAttribute('aria-expanded') === 'true';

            cerrarMenu();

            if (estabaAbierto && !carrito?.open) {
                event.preventDefault();
                menuToggle.focus();
            }
        });

        escritorio.addEventListener('change', cerrarMenu);
        window.addEventListener('pageshow', cerrarMenu);

        [categorias, cuenta].filter(Boolean).forEach((desplegable) => {
            desplegable.addEventListener('toggle', () => {
                if (!desplegable.open || !escritorio.matches) return;
                [categorias, cuenta].forEach((otro) => {
                    if (otro && otro !== desplegable) otro.open = false;
                });
            });
        });
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
                    form.action,
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
                    const fallo = await response.json().catch(() => ({}));
                    throw new Error(
                        Object.values(fallo.errors || {}).flat()[0] || fallo.message || 'No se pudo agregar el producto. Inténtalo nuevamente.'
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

                const panel = await fetch(contenido.dataset.url, {
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
        navbar?.classList.toggle('is-scrolled', window.scrollY > 16);
        // Sin portada, los controles siempre están visibles.
        // En el inicio aparecen cuando el video sale de la pantalla.
        const visible =
            !portada || portada.getBoundingClientRect().bottom <= (navbar?.offsetHeight || 112) + 16;

        if (visible === estadoAnterior) return;

        estadoAnterior = visible;

        mostrarElemento(
            navbar,
            visible,
            '-translate-y-full'
        );

        if (!visible) {
            document.dispatchEvent(new Event('bytestore:cerrar-menu'));
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

function iniciarPagoYape() {
    const form = document.querySelector('[data-payment-form]');
    if (!form) return;
    const archivo = form.querySelector('[data-payment-file]');
    const vista = form.querySelector('[data-payment-preview]');
    const boton = form.querySelector('[data-payment-submit]');
    const texto = boton.textContent;
    let imagenTemporal;
    let enviando = false;

    const tipoComprobante = form.querySelector('[data-payment-receipt-type]');
    const metodoPago = document.querySelector('[data-payment-method]');
    const documento = form.querySelector('[data-payment-document]');
    const razonSocial = form.querySelector('[data-payment-business-name]');
    const actualizarComprobante = () => {
        const factura = tipoComprobante.value === 'factura';
        documento.required = factura;
        documento.pattern = factura ? '[0-9]{11}' : '(?:[0-9]{8}|[0-9]{11})';
        form.querySelector('[data-payment-document-label]').textContent = factura ? 'RUC (11 dígitos)' : 'DNI o RUC (opcional)';
        form.querySelector('[data-payment-business-label]').hidden = !factura;
        razonSocial.disabled = !factura;
        razonSocial.required = factura;
    };
    tipoComprobante.addEventListener('change', actualizarComprobante);
    actualizarComprobante();

    if (metodoPago) {
        const actualizarMetodoPago = () => {
            document.querySelectorAll('[data-payment-method-details]').forEach((detalle) => {
                detalle.hidden = detalle.dataset.paymentMethodDetails !== metodoPago.value;
            });
        };
        metodoPago.addEventListener('change', actualizarMetodoPago);
        actualizarMetodoPago();
    }

    archivo.addEventListener('change', () => {
        if (imagenTemporal) URL.revokeObjectURL(imagenTemporal);
        vista.hidden = true;
        vista.removeAttribute('src');
        archivo.setCustomValidity('');
        const imagen = archivo.files[0];
        if (!imagen) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(imagen.type) || imagen.size > 5 * 1024 * 1024) {
            archivo.setCustomValidity('Elige una imagen JPG, PNG o WebP de máximo 5 MB.');
            archivo.reportValidity();
            return;
        }
        imagenTemporal = URL.createObjectURL(imagen);
        vista.src = imagenTemporal;
        vista.hidden = false;
    });

    form.addEventListener('submit', (event) => {
        if (enviando) { event.preventDefault(); return; }
        enviando = true;
        boton.disabled = true;
        boton.textContent = 'Enviando tu constancia…';
    });

    window.addEventListener('pageshow', () => {
        enviando = false;
        boton.disabled = false;
        boton.textContent = texto;
        actualizarComprobante();
    });
}

function iniciarAnimacionesDesplazamiento() {
    const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!('IntersectionObserver' in window) || !HTMLElement.prototype.animate) return;

    const animaciones = new Set();
    let observador;

    function configurar() {
        observador?.disconnect();
        animaciones.forEach((animacion) => animacion.cancel());
        animaciones.clear();
        if (movimientoReducido.matches) return;

        observador = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (!entrada.isIntersecting) return;
                const elemento = entrada.target;
                observador.unobserve(elemento);
                if (elemento.dataset.bsRevealed) return;
                elemento.dataset.bsRevealed = 'true';
                const animacion = elemento.animate([
                    { opacity: 0, transform: 'translateY(18px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ], {
                    duration: 450,
                    delay: Number(elemento.dataset.bsRevealDelay || 0),
                    easing: 'cubic-bezier(.2,.7,.3,1)',
                    fill: 'backwards',
                });
                animaciones.add(animacion);
                animacion.finished.catch(() => {}).finally(() => animaciones.delete(animacion));
            });
        }, { threshold: 0.08 });

        document.querySelectorAll('main article, main section:not(#inicio):not(.bs-banners), main header, main .catalog-toolbar')
            .forEach((elemento, indice) => {
                if (elemento.querySelector('article') || elemento.dataset.bsRevealed) return;
                elemento.dataset.bsRevealDelay = elemento.matches('article') ? String((indice % 4) * 55) : '0';
                observador.observe(elemento);
            });
    }

    configurar();
    movimientoReducido.addEventListener('change', configurar);
}

function iniciarPagina() {
    iniciarBuscadorCatalogo();
    configurarVisibilidadPortada();
    iniciarByteStore();
    iniciarPagoYape();
    iniciarAnimacionesDesplazamiento();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarPagina, {
        once: true,
    });
} else {
    iniciarPagina();
}
