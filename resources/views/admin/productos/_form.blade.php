@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Revisa los siguientes campos:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-7">
        <div class="form-group">
            <label for="categoria_id">Categoría</label>

            <select
                id="categoria_id"
                name="categoria_id"
                class="form-control"
                required
            >
                <option value="">Seleccione una categoría</option>

                @foreach ($categorias as $categoria)
                    <option
                        value="{{ $categoria->id }}"
                        @selected(
                            old('categoria_id', $producto->categoria_id ?? '')
                            == $categoria->id
                        )
                    >
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre del producto</label>

            <input
                id="nombre"
                type="text"
                name="nombre"
                value="{{ old('nombre', $producto->nombre ?? '') }}"
                class="form-control"
                maxlength="255"
                required
            >
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>

            <textarea
                id="descripcion"
                name="descripcion"
                class="form-control"
                rows="6"
            >{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="precio">Precio (S/)</label>

                    <input
                        id="precio"
                        type="number"
                        name="precio"
                        value="{{ old('precio', $producto->precio ?? '') }}"
                        class="form-control"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="stock">Stock</label>

                    <input
                        id="stock"
                        type="number"
                        name="stock"
                        value="{{ old('stock', $producto->stock ?? '') }}"
                        class="form-control"
                        min="0"
                        step="1"
                        required
                    >
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="border rounded p-3 mb-4">
            <h5>Foto principal</h5>

            <p class="text-muted small">
                Esta imagen aparecerá en el catálogo.
            </p>

            @if (!empty($producto->imagen))
                <img
                    src="{{ asset('storage/' . $producto->imagen) }}"
                    alt="Foto principal de {{ $producto->nombre }}"
                    class="foto-producto-admin mb-3"
                >
            @endif

            <label for="imagen">
                {{ isset($producto) ? 'Reemplazar foto principal' : 'Seleccionar foto principal' }}
            </label>

            <input
                id="imagen"
                type="file"
                name="imagen"
                accept="image/jpeg,image/png,image/webp"
                class="form-control-file"
            >

            @isset($producto)
                <small class="d-block text-muted mt-2">
                    Si no seleccionas otra imagen, conservaremos la actual.
                </small>
            @endisset
        </div>

        @include('admin.productos._galeria')
    </div>
</div>

<div class="border-top mt-4 pt-3">
    <button type="submit" class="btn btn-primary">
        {{ isset($producto) ? 'Guardar cambios' : 'Crear producto' }}
    </button>

    <a
        href="{{ route('productos.index') }}"
        class="btn btn-outline-secondary ml-2"
    >
        Volver
    </a>
</div>

@once
    @push('css')
        <style>
            .foto-producto-admin {
                display: block;
                width: 100%;
                max-width: 240px;
                aspect-ratio: 1 / 1;
                margin-inline: auto;
                object-fit: contain;
                background: #f8fafc;
                border-radius: 8px;
            }
        </style>
    @endpush
@endonce