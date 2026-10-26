@extends('layouts.admin')

@section('title', 'Categorías')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Categorías', 'descripcion' => 'Organiza los productos para que tus clientes encuentren lo que buscan.'])
@stop

@section('content')

<div class="card">

    <div class="card-header">

        <a href="{{ route('categorias.create') }}"
           class="btn btn-primary">
            Nueva Categoría
        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive"><table class="table bs-admin-list-table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

            @forelse($categorias as $categoria)

            <tr>

                <td>{{ $categoria->id }}</td>

                <td>{{ $categoria->nombre }}</td>

                <td>{{ $categoria->descripcion }}</td>

            <td>

             <a href="{{ route('categorias.edit',$categoria->id) }}"
              class="btn btn-warning btn-sm">
               Editar
             </a>

        <form action="{{ route('categorias.destroy',$categoria->id) }}"
          method="POST"
          style="display:inline;">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="btn btn-danger btn-sm"
                onclick="return confirm('¿Deseas eliminar esta categoría?')">
            Eliminar
        </button>

    </form>

            </td>

            </tr>

            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Aún no hay categorías. Crea una para organizar los productos.</td></tr>
            @endforelse

            </tbody>

        </table></div>

    </div>

</div>

@stop
