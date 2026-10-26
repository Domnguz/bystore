@extends('layouts.admin')

@section('title', 'Mi cuenta | ByteStore')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Mi cuenta', 'descripcion' => 'Mantén tus datos actualizados y administra el acceso a tu cuenta.'])
@stop

@section('content')
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Revisa los siguientes campos:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if(! $usuario->hasVerifiedEmail())
        <div class="alert alert-warning">Tu correo aún no está verificado. <a href="{{ route('verification.notice') }}">Verificar mi correo</a>.</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <form action="{{ route('profile.update') }}" method="POST" class="card">
                @csrf @method('PUT')
                <div class="card-header"><h2 class="card-title">Datos personales</h2></div>
                <div class="card-body">
                    <div class="row">
                        @foreach(['name' => 'Nombre', 'apellidos' => 'Apellidos', 'email' => 'Correo electrónico', 'telefono' => 'Teléfono'] as $campo => $etiqueta)
                            <div class="col-md-6 form-group">
                                <label for="{{ $campo }}">{{ $etiqueta }}</label>
                                <input id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : ($campo === 'telefono' ? 'tel' : 'text') }}" value="{{ old($campo, $usuario->{$campo}) }}" @required(in_array($campo, ['name', 'email'])) maxlength="{{ $campo === 'name' ? 100 : ($campo === 'apellidos' ? 150 : ($campo === 'telefono' ? 20 : 255)) }}" class="form-control">
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <input id="dni" name="dni" type="text" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" value="{{ $usuario->dni ?: old('dni') }}" @readonly((bool) $usuario->dni) required aria-describedby="perfil-dni-ayuda" class="form-control">
                        <small id="perfil-dni-ayuda" class="form-text text-muted">
                            @if($usuario->dni)
                                El DNI identifica tu cuenta y no se puede cambiar aquí. Si necesitas corregirlo, <a href="{{ route('contacto') }}">contacta al negocio</a>.
                            @else
                                Completa los 8 dígitos de tu DNI. Si ya tienes otra cuenta con este documento, <a href="{{ route('password.request') }}">recupera tu acceso</a>.
                            @endif
                        </small>
                    </div>
                    @if(! $usuario->dni)
                        <label for="privacy_accepted" class="d-flex align-items-start mb-3" style="gap:10px">
                            <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" @checked(old('privacy_accepted')) required class="mt-1">
                            <span>He leído el <a href="{{ route('privacidad') }}" target="_blank" rel="noopener">aviso de privacidad</a> y acepto el uso de mi DNI para evitar cuentas duplicadas.</span>
                        </label>
                    @endif
                    <button class="btn btn-primary">Guardar datos</button>
                </div>
            </form>
        </div>
        <div class="col-lg-5">
            <form action="{{ route('profile.password') }}" method="POST" class="card">
                @csrf @method('PUT')
                <div class="card-header"><h2 class="card-title">Cambiar contraseña</h2></div>
                <div class="card-body">
                    <div class="form-group"><label for="current_password">Contraseña actual</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="form-control"></div>
                    <div class="form-group"><label for="new_password">Nueva contraseña</label><input id="new_password" name="password" type="password" autocomplete="new-password" minlength="10" required aria-describedby="contrasena-ayuda" class="form-control"><small id="contrasena-ayuda" class="form-text text-muted">Al menos 10 caracteres, con letras y números.</small></div>
                    <div class="form-group"><label for="password_confirmation">Repite la nueva contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-control"></div>
                    <button class="btn btn-primary">Actualizar contraseña</button>
                </div>
            </form>
        </div>
    </div>
@stop
