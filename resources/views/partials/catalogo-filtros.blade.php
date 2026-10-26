@php
    $filtrosActivos = request()->filled('q') || request()->filled('min') || request()->filled('max') || request()->filled('orden') || request()->boolean('disponible');
    $filtrosAvanzados = request()->filled('min') || request()->filled('max') || request()->filled('orden') || request()->boolean('disponible');
@endphp

<details class="catalog-search" data-catalog-search>
    <summary class="catalog-search-toggle" aria-label="Abrir buscador de productos" title="Buscar productos">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path stroke-linecap="round" d="m16 16 4.5 4.5" />
        </svg>
        @if ($filtrosActivos)<span class="catalog-search-dot" aria-hidden="true"></span>@endif
    </summary>

    <div class="catalog-search-panel">
        <div class="catalog-search-heading">
            <div>
                <h2>Encuentra tu próxima mejora</h2>
                <p>Busca por nombre o modelo.</p>
            </div>
            <button type="button" class="catalog-search-close" data-search-close aria-label="Cerrar buscador">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M6 18 18 6" /></svg>
            </button>
        </div>

        <form method="GET" action="{{ url()->current() }}" role="search" aria-label="Buscar y filtrar productos">
            <label for="buscar-producto" class="sr-only">Buscar producto</label>
            <div class="catalog-search-autocomplete" data-catalog-autocomplete data-suggestions-url="{{ route('catalogo.sugerencias') }}">
                <div class="catalog-search-field">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path stroke-linecap="round" d="m16 16 4.5 4.5" /></svg>
                    <input id="buscar-producto" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="Nombre, marca, modelo o categoría" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="sugerencias-productos" aria-expanded="false" data-autocomplete-input>
                    <button type="submit" class="catalog-search-submit">Buscar</button>
                </div>
                <div id="sugerencias-productos" class="catalog-search-suggestions" role="listbox" aria-label="Productos sugeridos" hidden data-autocomplete-results></div>
            </div>

            <details class="catalog-search-filters" @if ($filtrosAvanzados) open @endif>
                <summary>Más filtros <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg></summary>
                <div class="catalog-search-filter-grid">
                    <label for="precio-min">Precio mínimo (S/)
                        <input id="precio-min" name="min" type="number" min="0" max="999999.99" step="0.01" value="{{ request('min') }}" placeholder="Desde">
                    </label>
                    <label for="precio-max">Precio máximo (S/)
                        <input id="precio-max" name="max" type="number" min="0" max="999999.99" step="0.01" value="{{ request('max') }}" placeholder="Hasta">
                    </label>
                    <label for="orden-productos" class="catalog-search-order">Ordenar por
                        <select id="orden-productos" name="orden">
                            <option value="">Orden del catálogo</option>
                            @foreach (['nombre' => 'Nombre', 'precio_asc' => 'Menor precio', 'precio_desc' => 'Mayor precio', 'novedades' => 'Novedades'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(request('orden') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="catalog-search-stock"><input type="checkbox" name="disponible" value="1" @checked(request()->boolean('disponible'))> Solo disponibles</label>
            </details>
            @if ($filtrosActivos)
                <a href="{{ url()->current() }}" class="catalog-search-reset">Limpiar búsqueda y filtros</a>
            @endif
        </form>
    </div>
</details>
