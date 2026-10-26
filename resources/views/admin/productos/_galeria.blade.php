<div class="border rounded p-3 mt-3">
    <h5>Fotos adicionales</h5>

    <p class="small text-muted">
        Selecciona una foto en cada cuadro.
        Se guardarán cuando pulses Guardar.
        JPG, PNG o WebP; máximo 5 MB por foto.
    </p>

    <div class="galeria-carga">
        @for ($i = 0; $i < 8; $i++)
            <label class="foto-carga">
                <input
                    type="file"
                    name="imagenes[]"
                    accept="image/jpeg,image/png,image/webp"
                    class="foto-carga-input"
                >

                <img
                    class="foto-carga-preview"
                    alt="Vista previa de la foto {{ $i + 1 }}"
                    hidden
                >

                <span class="foto-carga-texto">
                    <strong aria-hidden="true">+</strong>
                    <span>Foto {{ $i + 1 }}</span>
                </span>
            </label>
        @endfor
    </div>

    <p class="small text-muted mt-2 mb-0">
        Puedes volver a pulsar un cuadro para cambiar su foto.
        Estos cuadros añaden fotos; las existentes aparecen debajo.
    </p>

    @isset($producto)
        @if ($producto->imagenes->isNotEmpty())
            <h6 class="mt-4">Fotos guardadas</h6>

            <div class="galeria-carga">
                @foreach ($producto->imagenes as $foto)
                    <div class="foto-guardada">
                        <img
                            src="{{ asset('storage/' . $foto->ruta) }}"
                            alt="Foto adicional {{ $loop->iteration }}"
                        >

                        <label class="small text-danger mt-2 mb-0">
                            <input
                                type="checkbox"
                                name="eliminar_imagenes[]"
                                value="{{ $foto->id }}"
                                @checked(in_array(
                                    $foto->id,
                                    old('eliminar_imagenes', [])
                                ))
                            >
                            Quitar
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    @endisset
</div>

@once
    @push('css')
        <style>
            .galeria-carga {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .foto-carga {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                aspect-ratio: 1;
                margin: 0;
                overflow: hidden;
                border: 2px dashed #cbd5e1;
                border-radius: 12px;
                background: #f8fafc;
                cursor: pointer;
            }

            .foto-carga:hover,
            .foto-carga:focus-within {
                border-color: var(--bs-admin-brand);
                background: #edf7fc;
            }

            .foto-carga-input {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
                z-index: 2;
            }

            .foto-carga-texto {
                display: flex;
                flex-direction: column;
                align-items: center;
                color: #64748b;
                font-size: 13px;
            }

            .foto-carga-texto strong {
                color: var(--bs-admin-brand);
                font-size: 32px;
                line-height: 1.2;
            }

            .foto-carga-preview {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            .foto-guardada {
                padding: 8px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
            }

            .foto-guardada img {
                display: block;
                width: 100%;
                aspect-ratio: 1;
                object-fit: contain;
            }

            @media (min-width: 576px) {
                .galeria-carga {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }
        </style>
    @endpush

    @push('js')
        <script>
            document.querySelectorAll('.foto-carga-input').forEach(input => {
                let urlAnterior = null;

                input.addEventListener('change', () => {
                    const cuadro = input.closest('.foto-carga');
                    const imagen = cuadro.querySelector('.foto-carga-preview');
                    const texto = cuadro.querySelector('.foto-carga-texto');
                    const archivo = input.files[0];

                    if (urlAnterior) {
                        URL.revokeObjectURL(urlAnterior);
                        urlAnterior = null;
                    }

                    imagen.hidden = true;
                    imagen.removeAttribute('src');
                    texto.hidden = false;
                    texto.style.display = '';

                    if (!archivo) return;

                    if (
                        !['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type)
                        || archivo.size > 5 * 1024 * 1024
                    ) {
                        input.value = '';
                        alert('Selecciona una imagen JPG, PNG o WebP de hasta 5 MB.');
                        return;
                    }

                    urlAnterior = URL.createObjectURL(archivo);
                    imagen.src = urlAnterior;
                    imagen.hidden = false;
                    texto.style.display = 'none';
                });
            });
        </script>
    @endpush
@endonce