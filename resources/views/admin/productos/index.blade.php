@extends('layouts.admin')

@section('title', 'Productos')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Productos', 'descripcion' => 'Administra tu catálogo, los precios y el stock disponible.'])
@stop

@section('content')

@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
<form method="GET" action="{{ route('productos.index') }}" class="card card-body mb-3" aria-label="Filtrar productos">
    <div class="form-row align-items-end">
        <div class="form-group col-md-4 mb-2">
            <label for="buscarProductos">Buscar productos</label>
            <input id="buscarProductos" type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" maxlength="100" class="form-control" placeholder="Nombre, marca, modelo o SKU">
        </div>
        <div class="form-group col-md-4 mb-2">
            <label for="categoriaFiltro">Categoría</label>
            <select id="categoriaFiltro" name="categoria_id" class="form-control">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(($filtros['categoria_id'] ?? '') == $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2 mb-2">
            <label class="d-flex align-items-center"><input type="checkbox" name="stock_bajo" value="1" class="mr-2" @checked(request()->boolean('stock_bajo'))> Solo stock bajo</label>
        </div>
        <div class="form-group col-md-2 mb-2 d-flex">
            <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
            @if(request()->query())<a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">Limpiar</a>@endif
        </div>
    </div>
</form>

<div class="card">

    <div class="card-header d-flex justify-content-between">

        <h3 class="card-title">Productos Registrados</h3>

        <a href="{{ route('productos.create') }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>

    </div>

    <div class="card-body">

        <h4>Total productos: {{ $productos->total() }}</h4>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="table-responsive"><table class="table bs-admin-list-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoria</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                @forelse($productos as $producto)
                <tr>

                    <td>{{ $producto->id }}</td>

                    <td>
                        @if($producto->imagen)
                            <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" width="58" class="img-thumbnail">
                        @else
                            <span class="bs-admin-product-placeholder" role="img" aria-label="Sin imagen"><i class="fas fa-image" aria-hidden="true"></i></span>
                        @endif
                    </td>

                    <td>{{ $producto->nombre }}</td>
                    
                    <td>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>

                    <td>S/ {{ number_format((float) $producto->precio, 2) }}</td>

                    <td>
                        @if($producto->stock <= $stockBajoLimite)
                            <span class="badge badge-warning">{{ $producto->stock }} · Bajo</span>
                        @else
                            {{ $producto->stock }}
                        @endif
                    </td>

                    <td>

                        <button type="button" class="btn btn-outline-success btn-sm mr-1 mb-1" title="Reponer stock de {{ $producto->nombre }}" data-stock-open data-action="{{ route('productos.reponer-stock', ['id' => $producto->id]) }}" data-product="{{ $producto->nombre }}" data-stock="{{ $producto->stock }}">
                            <i class="fas fa-boxes" aria-hidden="true"></i> Reponer stock
                        </button>

                        <a href="{{ route('productos.edit', $producto->id) }}"
                           class="btn btn-warning btn-sm" aria-label="Editar {{ $producto->nombre }}" title="Editar producto">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('productos.destroy', $producto->id) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-danger btn-sm" aria-label="Eliminar {{ $producto->nombre }}" title="Eliminar producto"
                                    onclick="return confirm('¿Está seguro de eliminar este producto?')">

                                <i class="fas fa-trash"></i>

                            </button>

                        </form>

                    </td>

                </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay productos que coincidan con estos filtros.</td></tr>
                @endforelse

            </tbody>

        </table></div>
        {{ $productos->links('pagination::bootstrap-4') }}

    </div>

</div>

<dialog id="modalReponerStock" class="bs-stock-dialog" aria-labelledby="modalReponerStockTitulo">
    <div class="bs-stock-dialog__panel">
        <div class="bs-stock-dialog__top">
            <span class="bs-stock-dialog__icon" aria-hidden="true"><i class="fas fa-boxes"></i></span>
            <button type="button" class="bs-stock-dialog__close" data-stock-close aria-label="Cerrar ventana">&times;</button>
        </div>
        <p class="bs-stock-dialog__eyebrow">Inventario de ByteStore</p>
        <h2 id="modalReponerStockTitulo">Reponer stock</h2>
        <p class="bs-stock-dialog__description">Registra las unidades que llegaron. Se sumarán al stock disponible del producto.</p>

        <div class="bs-stock-dialog__product">
            <span>Producto seleccionado</span>
            <strong data-stock-product></strong>
        </div>
        <div class="bs-stock-dialog__current">
            <span>Stock actual</span>
            <strong><span data-stock-current></span> unidades</strong>
        </div>

        <form id="formReponerStock" method="POST" action="">
            @csrf
            <label for="cantidadReposicion">Cantidad que deseas agregar</label>
            <div class="bs-stock-dialog__quantity">
                <i class="fas fa-plus" aria-hidden="true"></i>
                <input id="cantidadReposicion" type="number" name="cantidad" min="1" max="10000" value="1" required inputmode="numeric">
                <span>unidades</span>
            </div>
            <p class="bs-stock-dialog__preview" aria-live="polite">Nuevo stock: <strong data-stock-preview></strong> unidades</p>
            <div class="bs-stock-dialog__actions">
                <button type="button" class="btn btn-light" data-stock-close>Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1" aria-hidden="true"></i> Confirmar reposición</button>
            </div>
        </form>
    </div>
</dialog>

@section('js')
<script>
    (() => {
        const dialog = document.getElementById('modalReponerStock');
        const form = document.getElementById('formReponerStock');
        const cantidad = document.getElementById('cantidadReposicion');
        if (!dialog || !form || !cantidad || typeof dialog.showModal !== 'function') return;

        const producto = dialog.querySelector('[data-stock-product]');
        const stockActual = dialog.querySelector('[data-stock-current]');
        const stockNuevo = dialog.querySelector('[data-stock-preview]');
        let baseStock = 0;

        const actualizarVistaPrevia = () => {
            const unidades = Number.parseInt(cantidad.value, 10);
            stockNuevo.textContent = Number.isInteger(unidades) && unidades > 0 ? (baseStock + unidades).toLocaleString('es-PE') : '—';
        };

        document.querySelectorAll('[data-stock-open]').forEach((boton) => {
            boton.addEventListener('click', () => {
                form.action = boton.dataset.action;
                producto.textContent = boton.dataset.product;
                baseStock = Number.parseInt(boton.dataset.stock, 10) || 0;
                stockActual.textContent = baseStock.toLocaleString('es-PE');
                cantidad.value = '1';
                actualizarVistaPrevia();
                dialog.showModal();
                window.requestAnimationFrame(() => cantidad.focus());
            });
        });

        cantidad.addEventListener('input', actualizarVistaPrevia);
        dialog.querySelectorAll('[data-stock-close]').forEach((boton) => boton.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (evento) => {
            if (evento.target === dialog) dialog.close();
        });
    })();
</script>
@stop

@stop
