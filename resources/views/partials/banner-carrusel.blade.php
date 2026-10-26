@if ($banners->isNotEmpty())

    <section
        id="carruselBanners"
        class="bs-banners"
        aria-label="Banners de ByteStore"
        aria-roledescription="carrusel"
    >
        <div class="bs-banners-ventana">

            @foreach ($banners as $banner)
                <div
                    data-banner-slide
                    class="bs-banner {{ $loop->first ? 'es-activo' : '' }}"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                    role="group"
                    aria-label="{{ $loop->iteration }} de {{ $banners->count() }}"
                >
                    <picture>
                        {{-- Usa la imagen móvil si la subiste desde el admin. --}}
                        @if ($banner->imagen_movil)
                            <source
                                media="(max-width: 639px)"
                                srcset="{{ asset('storage/' . $banner->imagen_movil) }}"
                            >
                        @endif

                        <img
                            src="{{ asset('storage/' . $banner->imagen) }}"
                            alt="{{ $banner->titulo }}"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            decoding="async"
                            class="{{ $banner->imagen_movil ? 'tiene-version-movil' : '' }}"
                        >
                    </picture>
                </div>
            @endforeach

        </div>

        @if ($banners->count() > 1)
            {{-- Controles debajo para que no tapen el texto del banner. --}}
            <div class="bs-banner-controles">
                <div class="bs-banner-puntos">
                    @foreach ($banners as $banner)
                        <button
                            type="button"
                            data-banner-dot
                            aria-label="Mostrar banner {{ $loop->iteration }}"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            <span aria-hidden="true"></span>
                        </button>
                    @endforeach
                </div>

                <button
                    type="button"
                    data-banner-pause
                    class="bs-banner-pausa"
                >
                    Pausar
                </button>
            </div>
        @endif
    </section>

    <style>
        .bs-banners {
            margin: 16px 16px 0;
        }

        .bs-banners-ventana {
            position: relative;
            aspect-ratio: 3 / 1;
            overflow: hidden;
            border-radius: 12px;
            background: #020617;
        }

        .bs-banner {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 600ms ease, visibility 600ms ease;
        }

        .bs-banner.es-activo {
            opacity: 1;
            visibility: visible;
        }

        .bs-banner picture,
        .bs-banner img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .bs-banner img {
            object-fit: contain;
        }

        .bs-banner-controles {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
        }

        .bs-banner-puntos {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .bs-banner-puntos button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 44px;
            padding: 0;
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        .bs-banner-puntos span {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #94a3b8;
        }

        .bs-banner-puntos [aria-pressed="true"] span {
            width: 20px;
            background: var(--color-blue-600);
        }

        .bs-banner-pausa {
            min-height: 44px;
            padding: 0 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #475569;
            font-size: 12px;
            cursor: pointer;
        }

        .bs-banner-controles button:focus-visible {
            outline: 2px solid var(--color-blue-600);
            outline-offset: -2px;
        }

        @media (min-width: 1024px) {
            .bs-banners {
                margin: 20px 32px 0;
            }
        }

 @media (max-width: 639px) {
    .bs-banners {
        margin: 12px 10px 0;
    }

    .bs-banners-ventana {
        aspect-ratio: 2 / 1;
    }

    .bs-banner img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
}

        @media (prefers-reduced-motion: reduce) {
            .bs-banner {
                transition: none;
            }
        }
    </style>

    <script>
        (() => {
            function iniciarCarrusel() {
                const carrusel = document.getElementById('carruselBanners');

                if (!carrusel || carrusel.dataset.iniciado === 'true') return;

                carrusel.dataset.iniciado = 'true';

                const slides = [
                    ...carrusel.querySelectorAll('[data-banner-slide]')
                ];

                const puntos = [
                    ...carrusel.querySelectorAll('[data-banner-dot]')
                ];

                const botonPausa = carrusel.querySelector('[data-banner-pause]');

                // Con una sola imagen no hay otra a la que avanzar.
                if (slides.length < 2) return;

                let actual = 0;
                let temporizador = null;

                const preferencia = window.matchMedia(
                    '(prefers-reduced-motion: reduce)'
                );

                let pausado = preferencia.matches;

                function prepararImagen(indice) {
                    const imagen = slides[indice].querySelector('img');

                    // Carga también la siguiente imagen antes de mostrarla.
                    imagen.loading = 'eager';
                }

                function mostrar(indice) {
                    actual = (indice + slides.length) % slides.length;

                    prepararImagen(actual);
                    prepararImagen((actual + 1) % slides.length);

                    slides.forEach((slide, posicion) => {
                        const activo = posicion === actual;

                        slide.classList.toggle('es-activo', activo);
                        slide.setAttribute('aria-hidden', String(!activo));
                    });

                    puntos.forEach((punto, posicion) => {
                        punto.setAttribute(
                            'aria-pressed',
                            String(posicion === actual)
                        );
                    });
                }

                function programar() {
                    clearTimeout(temporizador);

                    if (pausado || document.hidden) return;

                    temporizador = setTimeout(() => {
                        mostrar(actual + 1);
                        programar();
                    }, 4500);
                }

                function actualizarBoton() {
                    botonPausa.textContent = pausado ? 'Reproducir' : 'Pausar';

                    botonPausa.setAttribute(
                        'aria-label',
                        pausado
                            ? 'Reproducir banners automáticamente'
                            : 'Pausar banners'
                    );
                }

                puntos.forEach((punto, indice) => {
                    punto.addEventListener('click', () => {
                        mostrar(indice);

                        // Elegir un punto reinicia el tiempo.
                        // Ya no deja el carrusel pausado permanentemente.
                        programar();
                    });
                });

                botonPausa.addEventListener('click', () => {
                    pausado = !pausado;
                    actualizarBoton();
                    programar();
                });

                document.addEventListener('visibilitychange', programar);

                preferencia.addEventListener('change', (evento) => {
                    pausado = evento.matches;
                    actualizarBoton();
                    programar();
                });

                mostrar(0);
                actualizarBoton();
                programar();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', iniciarCarrusel);
            } else {
                iniciarCarrusel();
            }
        })();
    </script>

@endif
