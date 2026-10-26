@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Usuarios', 'descripcion' => 'Consulta las cuentas de tus clientes y administra sus permisos.'])
@stop

@section('content')

<div class="card">

    <div class="card-header">

        <a href="{{ route('usuarios.create') }}"
           class="btn btn-primary">

            <i class="fas fa-plus"></i>
            Nuevo Usuario

        </a>

    </div>

    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="table-responsive"><table class="table table-hover bs-admin-list-table">

            <thead class="thead-dark">

                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th width="220">Acciones</th>
                </tr>

            </thead>

            <tbody>

            @forelse($usuarios as $usuario)

                <tr>

                    <td>{{ $usuario->id }}</td>

                    <td>

                        @if($usuario->foto)

                        <img src="{{ asset('storage/'.$usuario->foto) }}" alt="Foto de {{ $usuario->name }}" class="bs-admin-user-photo">


                        @else
                        <span class="bs-admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>

                        @endif

                    </td>

                    <td>{{ $usuario->name }}</td>

                    <td>{{ $usuario->email }}</td>

                    <td>

                        @if($usuario->rol == 'admin')

                            <span class="badge badge-danger">
                                Administrador
                            </span>

                        @else

                            <span class="badge badge-success">
                                Cliente
                            </span>

                        @endif

                    </td>

                    <td>

                    <a href="{{ route('usuarios.edit',$usuario->id) }}"
                    class="btn btn-warning btn-sm mr-2">

                        <i class="fas fa-edit"></i> Editar

                    </a>

                    <form action="{{ route('usuarios.destroy',$usuario->id) }}"
                        method="POST"
                        style="display:inline;"
                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm">

                            <i class="fas fa-trash"></i> Eliminar

                        </button>

                    </form>

                </td>

                </tr>

            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
            @endforelse

            </tbody>

        </table></div>
        {{ $usuarios->links('pagination::bootstrap-4') }}

    </div>

</div>

@stop
