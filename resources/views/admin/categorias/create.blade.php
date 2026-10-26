@extends('layouts.admin')

@section('title', 'Nueva Categoría')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Nueva categoría', 'descripcion' => 'Crea una categoría para organizar tu catálogo.'])
@stop

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Registrar Categoría</h3>
    </div>

    <form action="{{ route('categorias.store') }}" method="POST">

        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Nombre de la Categoría</label>

                <input type="text"
                       name="nombre"
                       class="form-control"
                       placeholder="Ejemplo: Polos"
                       required>
            </div>

            <div class="form-group">
                <label>Descripción</label>

                <textarea
                    name="descripcion"
                    class="form-control"
                    rows="4"
                    placeholder="Descripción de la categoría"></textarea>
            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                Guardar Categoría
            </button>

            <a href="{{ route('categorias.index') }}"
               class="btn btn-secondary">
                Cancelar
            </a>

        </div>

    </form>

</div>

@stop