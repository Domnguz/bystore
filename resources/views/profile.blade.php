@extends('layouts.app')
@section('title', 'Mi cuenta | ByteStore')
@section('contenido')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 flex flex-wrap justify-between gap-4">
        <h1 class="text-3xl font-bold">Mi cuenta</h1>
        <a href="{{ route('mis-pedidos.index') }}" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white">Mis pedidos</a>
    </div>
    @include('partials.form-errors')
    @if(! $usuario->hasVerifiedEmail())
        <p class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm">Tu correo aún no está verificado. <a href="{{ route('verification.notice') }}" class="text-blue-700 underline">Verificar mi correo</a> para realizar pedidos.</p>
    @endif
    <form action="{{ route('profile.update') }}" method="POST" class="rounded-2xl bg-white p-6">
        @csrf @method('PUT')
        <h2 class="mb-5 text-xl font-bold">Datos personales</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <label for="dni" class="block text-sm">DNI
                <input id="dni" name="dni" type="text" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" value="{{ $usuario->dni ?: old('dni') }}" @readonly((bool) $usuario->dni) required aria-describedby="perfil-dni-ayuda" class="mt-1 w-full rounded-lg border border-slate-300 p-3 read-only:bg-slate-100">
                <span id="perfil-dni-ayuda" class="mt-1 block text-slate-500">
                    @if($usuario->dni)
                        El DNI identifica tu cuenta y no se puede cambiar aquí. Si necesitas corregirlo, <a href="{{ route('contacto') }}" class="text-blue-700 underline">contacta al negocio</a>.
                    @else
                        Completa tus 8 dígitos antes de comprar. Si ya tienes otra cuenta con este DNI, <a href="{{ route('password.request') }}" class="text-blue-700 underline">recupera tu acceso</a>.
                    @endif
                </span>
            </label>
            @foreach(['name' => 'Nombre', 'apellidos' => 'Apellidos', 'email' => 'Correo', 'telefono' => 'Teléfono'] as $campo => $etiqueta)
                <label for="{{ $campo }}" class="block text-sm">{{ $etiqueta }}
                    <input id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" value="{{ old($campo, $usuario->{$campo}) }}" @required(in_array($campo, ['name', 'email'])) class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                </label>
            @endforeach
        </div>
        @if(! $usuario->dni)
            <label for="privacy_accepted" class="mt-5 flex items-start gap-3 text-sm">
                <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" @checked(old('privacy_accepted')) required class="mt-1">
                <span>He leído el <a href="{{ route('privacidad') }}" target="_blank" rel="noopener" class="text-blue-700 underline">aviso de privacidad</a> y acepto el uso de mi DNI para evitar cuentas duplicadas.</span>
            </label>
        @endif
        <button class="mt-5 min-h-12 rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white">Guardar datos</button>
    </form>
    <form action="{{ route('profile.password') }}" method="POST" class="mt-6 space-y-4 rounded-2xl bg-white p-6">
        @csrf @method('PUT')
        <h2 class="text-xl font-bold">Cambiar contraseña</h2>
        <label for="current_password" class="block text-sm">Contraseña actual
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <label for="new_password" class="block text-sm">Nueva contraseña
            <input id="new_password" name="password" type="password" autocomplete="new-password" minlength="10" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <p class="text-sm text-slate-500">Al menos 10 caracteres, con letras y números.</p>
        <label for="password_confirmation" class="block text-sm">Repite la nueva contraseña
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <button class="min-h-12 rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white">Actualizar contraseña</button>
    </form>
</div>
@endsection
