function iniciarUbicacion() {
    // Busca los elementos que colocamos en navbar.blade.php.
    const selector = document.getElementById('selectorUbicacion');
    const boton = document.getElementById('detectarUbicacion');
    const texto = document.getElementById('textoUbicacion');
    const estado = document.getElementById('estadoUbicacion');

    // Si no existe el selector en esta página, no continúa.
    if (!selector || !boton || !texto || !estado) return;

    // Evita iniciar la misma función dos veces.
    if (selector.dataset.iniciado === 'true') return;
    selector.dataset.iniciado = 'true';

    const clave = 'bytestore.ubicacion';
    const duracion = 30 * 60 * 1000; // 30 minutos.
    let ocupado = false;

    // Muestra mensajes de progreso o error.
    function informar(mensaje, esError = false) {
        estado.textContent = mensaje;
        estado.dataset.error = String(esError);
    }

    // Actualiza el nombre visible en el menú.
    function mostrarUbicacion(nombre) {
        texto.textContent = nombre;
        texto.title = `Última ubicación detectada: ${nombre}`;
        boton.textContent = 'Actualizar mi ubicación';
    }

    // Recupera el nombre guardado al navegar en la misma pestaña.
    // No guardamos coordenadas.
    try {
        const guardado = JSON.parse(sessionStorage.getItem(clave));

        if (
            guardado &&
            typeof guardado.nombre === 'string' &&
            Number.isFinite(guardado.fecha) &&
            Date.now() - guardado.fecha >= 0 &&
            Date.now() - guardado.fecha < duracion
        ) {
            mostrarUbicacion(guardado.nombre);
        } else {
            sessionStorage.removeItem(clave);
        }
    } catch {
        // Si el navegador bloquea el almacenamiento,
        // todavía podemos detectar y mostrar la ubicación.
    }

    // Solicita permiso al navegador y obtiene las coordenadas.
    function obtenerCoordenadas() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                resolve,
                reject,
                {
                    enableHighAccuracy: false,
                    timeout: 15000,
                    maximumAge: 0,
                }
            );
        });
    }

    // Consulta la ciudad y el país usando las coordenadas actuales.
    async function obtenerCiudad(coordenadas) {
        const url = new URL(
            'https://api.bigdatacloud.net/data/reverse-geocode-client'
        );

        url.searchParams.set('latitude', coordenadas.latitude);
        url.searchParams.set('longitude', coordenadas.longitude);
        url.searchParams.set('localityLanguage', 'es');

        // Cancela la consulta si tarda más de 10 segundos.
        const controlador = new AbortController();
        const temporizador = setTimeout(
            () => controlador.abort(),
            10000
        );

        try {
            const respuesta = await fetch(url, {
                signal: controlador.signal,
                credentials: 'omit',
                referrerPolicy: 'no-referrer',
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo consultar la ciudad.');
            }

            const datos = await respuesta.json();

            if (datos.error || !datos.countryName) {
                throw new Error('No se recibió una ubicación válida.');
            }

            const ciudad =
                datos.city ||
                datos.locality ||
                datos.principalSubdivision ||
                '';

            // Ejemplo de resultado: Tumbes, Perú.
            return [...new Set(
                [ciudad, datos.countryName].filter(Boolean)
            )].join(', ');
        } finally {
            clearTimeout(temporizador);
        }
    }

    // La ubicación se solicita únicamente al pulsar este botón.
    boton.addEventListener('click', async () => {
        if (ocupado) return;

        if (!window.isSecureContext) {
            informar(
                'La ubicación requiere HTTPS. En tu computadora puedes probar con localhost o 127.0.0.1.',
                true
            );
            return;
        }

        if (!navigator.geolocation) {
            informar(
                'Este navegador no permite detectar la ubicación.',
                true
            );
            return;
        }

        ocupado = true;
        boton.disabled = true;
        boton.setAttribute('aria-busy', 'true');

        informar('Solicitando tu ubicación…');

        let etapa = 'coordenadas';

        try {
            const posicion = await obtenerCoordenadas();

            etapa = 'ciudad';
            informar('Identificando tu ciudad…');

            const nombre = await obtenerCiudad(posicion.coords);

            mostrarUbicacion(nombre);
            informar(`Ubicación aproximada: ${nombre}.`);

            // Conserva solamente el nombre y la fecha de detección.
            try {
                sessionStorage.setItem(
                    clave,
                    JSON.stringify({
                        nombre,
                        fecha: Date.now(),
                    })
                );
            } catch {
                // No guardar el nombre no impide mostrarlo.
            }
        } catch (error) {
            if (etapa === 'coordenadas') {
                const mensajes = {
                    1: 'No se concedió el permiso. Puedes habilitar la ubicación en los permisos de este sitio, junto a la dirección del navegador.',
                    2: 'No pudimos detectar tu ubicación. Comprueba que la ubicación del dispositivo esté activada.',
                    3: 'La detección tardó demasiado. Inténtalo nuevamente.',
                };

                informar(
                    mensajes[error.code] ||
                    'No pudimos obtener tu ubicación. Inténtalo nuevamente.',
                    true
                );
            } else {
                informar(
                    'Obtuvimos tus coordenadas, pero no pudimos consultar la ciudad. Revisa tu conexión e inténtalo nuevamente.',
                    true
                );
            }
        } finally {
            ocupado = false;
            boton.disabled = false;
            boton.removeAttribute('aria-busy');
        }
    });

    // Cierra el panel cuando pulsas fuera de él.
    document.addEventListener('click', (evento) => {
        if (!selector.contains(evento.target)) {
            selector.open = false;
        }
    });

    // Cierra el panel al presionar Escape.
    selector.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            selector.open = false;
            selector.querySelector('summary').focus();
        }
    });
}

// Inicia cuando el HTML esté listo.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarUbicacion, {
        once: true,
    });
} else {
    iniciarUbicacion();
}