@extends('layouts.admin')

@section('title', 'Nuevo Usuario')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Nuevo usuario', 'descripcion' => 'Registra una cuenta y asigna el acceso correspondiente.'])
@stop

@section('content')

<div class="card">

    <div class="card-body">

        <form action="{{ route('usuarios.store') }}"
              method="POST"
              enctype="multipart/form-data" class="bs-admin-user-form">

            @csrf
<div class="form-group">
    <label>Nombre</label>

    <input type="text"
           name="name"
           class="form-control"
           required>
</div>

 <div class="form-group">
    <label>Apellidos</label>

    <input type="text"
           name="apellidos"
           class="form-control">
</div>

<div class="form-group">

    <label>DNI</label>

    <input type="text"
           name="dni"
           class="form-control"
           maxlength="8"
           minlength="8"
           required>

</div>

 <div class="form-group">

    <label>Teléfono</label>

    <input type="text"
           name="telefono"
           class="form-control"
           maxlength="9"
           required>

</div>

            <div class="form-group">

                <label>Género</label>

                <select name="genero"
                        class="form-control"
                        required>

                    <option value="">
                        Seleccione
                    </option>

                    <option value="Masculino">
                        Masculino
                    </option>

                    <option value="Femenino">
                        Femenino
                    </option>

                </select>

            </div>

            <div class="form-group">
                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>

                <input type="password"
                       name="password"
                       class="form-control"
                       required>
            </div>

            <div class="form-group">
                <label>Rol</label>

                <select name="rol"
                        class="form-control">

                    <option value="cliente">
                        Cliente
                    </option>

                    <option value="admin">
                        Administrador
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Foto</label>

                <input type="file"
                       name="foto"
                       class="form-control">

            </div>

            <button class="btn btn-success">

                Guardar Usuario

            </button>

        </form>

    </div>

</div>

@stop
