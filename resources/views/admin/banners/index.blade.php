@extends('layouts.admin')

@section('title', 'Banners | ByteStore')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Banners del catálogo', 'descripcion' => 'Elige las imágenes que tus clientes verán al entrar a la tienda.'])
@stop

@section('content')

    {{-- Mensajes después de guardar o eliminar. --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>No se guardaron los cambios:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <p class="mt-2 mb-0">
                Revisa los datos y vuelve a seleccionar las fotos.
            </p>
        </div>
    @endif

    <div class="alert alert-light border">
        <strong>Organiza tus banners</strong>

        <p class="mb-1 mt-2">
            El número de orden indica cuál aparece primero.
            Usa 1, 2, 3… y guarda cada banner por separado.
        </p>

        <p class="mb-0">
            Principal: 1800 × 600 px.
            Móvil opcional: 900 × 900 px.
            JPG, PNG o WebP, máximo 5 MB por imagen.
        </p>
    </div>

    {{-- =====================================================
        BANNERS EXISTENTES
        El controlador ya los entrega ordenados.
    ===================================================== --}}
    <h2 class="h5 mb-3">Banners guardados</h2>

    <div class="row">
        @forelse ($banners as $banner)
            <div class="col-md-6 col-xl-4">
                <div class="card">

                    <div class="card-header">
                        <strong>Orden {{ $banner->orden }}</strong>

                        <span class="badge {{ $banner->activo ? 'badge-primary' : 'badge-secondary' }} float-right">
                            {{ $banner->activo ? 'Activo' : 'Oculto' }}
                        </span>
                    </div>

                    <div class="card-body">

                        {{-- Formulario para modificar este banner. --}}
                        <form
                            action="{{ route('admin.banners.update', $banner) }}"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            @csrf
                            @method('PUT')

                            {{-- FOTO PRINCIPAL --}}
                            <div
                                class="foto-selector mb-3"
                                data-foto-selector
                                data-original="{{ asset('storage/' . $banner->imagen) }}"
                            >
                                <label
                                    for="principal-{{ $banner->id }}"
                                    class="font-weight-bold"
                                >
                                    Foto principal
                                </label>

                                <label
                                    for="principal-{{ $banner->id }}"
                                    class="foto-cuadro"
                                >
                                    <img
                                        data-preview
                                        src="{{ asset('storage/' . $banner->imagen) }}"
                                        alt="Banner: {{ $banner->titulo }}"
                                    >

                                    <span data-placeholder hidden>
                                        + Seleccionar foto
                                    </span>
                                </label>

                                <input
                                    id="principal-{{ $banner->id }}"
                                    type="file"
                                    name="imagen"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="foto-input"
                                    data-foto-input
                                    aria-label="Reemplazar foto principal"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary mt-2"
                                    data-quitar-seleccion
                                    hidden
                                >
                                    Cancelar nueva foto
                                </button>
                            </div>

                            {{-- FOTO MÓVIL --}}
                            <div
                                class="foto-selector mb-3"
                                data-foto-selector
                                data-original="{{ $banner->imagen_movil ? asset('storage/' . $banner->imagen_movil) : '' }}"
                            >
                                <label
                                    for="movil-{{ $banner->id }}"
                                    class="font-weight-bold"
                                >
                                    Foto móvil — opcional
                                </label>

                                <label
                                    for="movil-{{ $banner->id }}"
                                    class="foto-cuadro foto-cuadro-movil"
                                >
                                    <img
                                        data-preview
                                        @if ($banner->imagen_movil)
                                            src="{{ asset('storage/' . $banner->imagen_movil) }}"
                                        @else
                                            hidden
                                        @endif
                                        alt="Vista previa para celular"
                                    >

                                    <span
                                        data-placeholder
                                        @if ($banner->imagen_movil) hidden @endif
                                    >
                                        + Seleccionar foto móvil
                                    </span>
                                </label>

                                <input
                                    id="movil-{{ $banner->id }}"
                                    type="file"
                                    name="imagen_movil"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="foto-input"
                                    data-foto-input
                                    aria-label="Agregar o reemplazar foto móvil"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary mt-2"
                                    data-quitar-seleccion
                                    hidden
                                >
                                    Cancelar nueva foto
                                </button>
                            </div>

                            @if ($banner->imagen_movil)
                                <div class="custom-control custom-checkbox mb-3">
                                    <input
                                        id="quitar-movil-{{ $banner->id }}"
                                        type="checkbox"
                                        name="quitar_imagen_movil"
                                        value="1"
                                        class="custom-control-input"
                                    >

                                    <label
                                        class="custom-control-label"
                                        for="quitar-movil-{{ $banner->id }}"
                                    >
                                        Eliminar foto móvil al guardar
                                    </label>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="titulo-{{ $banner->id }}">
                                    Descripción
                                </label>

                                <input
                                    id="titulo-{{ $banner->id }}"
                                    name="titulo"
                                    value="{{ $banner->titulo }}"
                                    maxlength="255"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="row">
                                <div class="col-6 form-group">
                                    <label for="orden-{{ $banner->id }}">
                                        Orden
                                    </label>

                                    <input
                                        id="orden-{{ $banner->id }}"
                                        type="number"
                                        name="orden"
                                        value="{{ $banner->orden }}"
                                        min="0"
                                        max="9999"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="col-6 form-group">
                                    <label for="activo-{{ $banner->id }}">
                                        Estado
                                    </label>

                                    <select
                                        id="activo-{{ $banner->id }}"
                                        name="activo"
                                        class="form-control"
                                    >
                                        <option value="1" @selected($banner->activo)>
                                            Activo
                                        </option>

                                        <option value="0" @selected(!$banner->activo)>
                                            Oculto
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                Guardar cambios
                            </button>
                        </form>

                        {{-- Elimina el banner completo y sus archivos. --}}
                        <form
                            action="{{ route('admin.banners.destroy', $banner) }}"
                            method="POST"
                            class="mt-2"
                            onsubmit="return confirm('¿Eliminar este banner y sus fotos guardadas?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-outline-danger btn-block"
                            >
                                Eliminar banner
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        @empty
            <div class="col-12">
                <p class="text-muted">
                    Todavía no hay banners guardados.
                    Usa los espacios de abajo para agregar fotos.
                </p>
            </div>
        @endforelse
    </div>

    {{-- =====================================================
        ESPACIOS PARA NUEVOS BANNERS
        Cada cuadro se guarda de manera independiente.
    ===================================================== --}}
    <h2 class="h5 mt-4 mb-3">Agregar nuevos banners</h2>

    @php
        $siguienteOrden = (int) ($banners->max('orden') ?? 0) + 1;
    @endphp

    <div class="row">

        @for ($espacio = 0; $espacio < 4; $espacio++)
            <div class="col-md-6 col-xl-3">
                <div class="card">

                    <div class="card-header">
                        <strong>Nuevo banner {{ $espacio + 1 }}</strong>
                    </div>

                    <div class="card-body">

                        <form
                            action="{{ route('admin.banners.store') }}"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            @csrf

                            {{-- Selector de la imagen principal. --}}
                            <div
                                class="foto-selector mb-3"
                                data-foto-selector
                                data-original=""
                            >
                                <label
                                    for="nueva-principal-{{ $espacio }}"
                                    class="font-weight-bold"
                                >
                                    Foto principal
                                </label>

                                <label
                                    for="nueva-principal-{{ $espacio }}"
                                    class="foto-cuadro"
                                >
                                    <img
                                        data-preview
                                        alt="Vista previa del nuevo banner"
                                        hidden
                                    >

                                    <span data-placeholder>
                                        + Seleccionar foto
                                    </span>
                                </label>

                                <input
                                    id="nueva-principal-{{ $espacio }}"
                                    type="file"
                                    name="imagen"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="foto-input"
                                    data-foto-input
                                    required
                                    aria-label="Seleccionar foto principal"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger mt-2"
                                    data-quitar-seleccion
                                    hidden
                                >
                                    Quitar foto seleccionada
                                </button>
                            </div>

                            {{-- Selector de la imagen para celular. --}}
                            <div
                                class="foto-selector mb-3"
                                data-foto-selector
                                data-original=""
                            >
                                <label
                                    for="nueva-movil-{{ $espacio }}"
                                    class="font-weight-bold"
                                >
                                    Foto móvil — opcional
                                </label>

                                <label
                                    for="nueva-movil-{{ $espacio }}"
                                    class="foto-cuadro foto-cuadro-movil"
                                >
                                    <img
                                        data-preview
                                        alt="Vista previa para celular"
                                        hidden
                                    >

                                    <span data-placeholder>
                                        + Foto móvil
                                    </span>
                                </label>

                                <input
                                    id="nueva-movil-{{ $espacio }}"
                                    type="file"
                                    name="imagen_movil"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="foto-input"
                                    data-foto-input
                                    aria-label="Seleccionar foto móvil opcional"
                                >

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger mt-2"
                                    data-quitar-seleccion
                                    hidden
                                >
                                    Quitar foto seleccionada
                                </button>
                            </div>

                            <div class="form-group">
                                <label for="nuevo-titulo-{{ $espacio }}">
                                    Descripción
                                </label>

                                <input
                                    id="nuevo-titulo-{{ $espacio }}"
                                    name="titulo"
                                    maxlength="255"
                                    class="form-control"
                                    placeholder="Describe el banner"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="nuevo-orden-{{ $espacio }}">
                                    Orden
                                </label>

                                <input
                                    id="nuevo-orden-{{ $espacio }}"
                                    type="number"
                                    name="orden"
                                    value="{{ $siguienteOrden + $espacio }}"
                                    min="0"
                                    max="9999"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="nuevo-estado-{{ $espacio }}">
                                    Estado
                                </label>

                                <select
                                    id="nuevo-estado-{{ $espacio }}"
                                    name="activo"
                                    class="form-control"
                                >
                                    <option value="1">Activo</option>
                                    <option value="0">Oculto</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                Guardar este banner
                            </button>

                        </form>

                    </div>
                </div>
            </div>
        @endfor

    </div>

    <p class="text-muted pb-3">
        Guarda cada banner antes de seleccionar fotos en otro espacio:
        al guardar, la página se recarga y las selecciones sin guardar se pierden.
    </p>

@stop

@section('css')
    <style>
        /* Contenedor del selector de fotos. */
        .foto-selector {
            position: relative;
        }

        /* Cuadro que se puede pulsar para seleccionar una imagen. */
        .foto-cuadro {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            aspect-ratio: 3 / 1;
            min-height: 100px;
            margin: 0;
            overflow: hidden;
            border: 2px dashed #94a3b8;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
        }

        .foto-cuadro:hover {
            border-color: var(--bs-admin-brand);
            background: #edf7fc;
        }

        .foto-cuadro-movil {
            width: 140px;
            max-width: 100%;
            aspect-ratio: 1;
        }

        .foto-cuadro img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .foto-cuadro span {
            padding: 12px;
            color: #475569;
            text-align: center;
            font-size: 13px;
        }

        /* Oculta visualmente el input, conservando acceso por teclado. */
        .foto-input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            overflow: hidden;
            clip-path: inset(50%);
            white-space: nowrap;
            border: 0;
        }

        .foto-selector:focus-within .foto-cuadro {
            outline: 2px solid var(--bs-admin-brand);
            outline-offset: 3px;
        }

        /* Asegura que los elementos marcados hidden no se muestren. */
        .foto-selector [hidden] {
            display: none !important;
        }
    </style>
@stop

@section('js')
    <script>
        document.querySelectorAll('[data-foto-selector]').forEach((selector) => {
            const input = selector.querySelector('[data-foto-input]');
            const imagen = selector.querySelector('[data-preview]');
            const texto = selector.querySelector('[data-placeholder]');
            const quitar = selector.querySelector('[data-quitar-seleccion]');
            const original = selector.dataset.original;

            let urlTemporal = null;

            function liberarVistaPrevia() {
                if (urlTemporal) {
                    URL.revokeObjectURL(urlTemporal);
                    urlTemporal = null;
                }
            }

            function restaurar() {
                liberarVistaPrevia();
                input.value = '';

                // Al cancelar un reemplazo, vuelve a mostrar la foto guardada.
                if (original) {
                    imagen.src = original;
                    imagen.hidden = false;
                    texto.hidden = true;
                } else {
                    imagen.removeAttribute('src');
                    imagen.hidden = true;
                    texto.hidden = false;
                }

                quitar.hidden = true;
            }

            input.addEventListener('change', () => {
                const archivo = input.files[0];

                if (!archivo) {
                    restaurar();
                    return;
                }

                const tipos = ['image/jpeg', 'image/png', 'image/webp'];

                if (!tipos.includes(archivo.type)) {
                    restaurar();
                    alert('Selecciona una imagen JPG, PNG o WebP.');
                    return;
                }

                if (archivo.size > 5 * 1024 * 1024) {
                    restaurar();
                    alert('La imagen debe pesar como máximo 5 MB.');
                    return;
                }

                liberarVistaPrevia();

                // Muestra la foto local; todavía no se ha guardado en Laravel.
                urlTemporal = URL.createObjectURL(archivo);
                imagen.src = urlTemporal;
                imagen.hidden = false;
                texto.hidden = true;
                quitar.hidden = false;
            });

            quitar.addEventListener('click', restaurar);
        });
    </script>
@stop