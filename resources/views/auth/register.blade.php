@extends('layouts.app')
@section('title', 'Crear cuenta | ByteStore')
@section('contenido')
<div class="mx-auto max-w-md rounded-2xl bg-white p-6 shadow-sm">
    <h1 class="mb-6 text-2xl font-bold">Crea tu cuenta</h1>
    <p class="mb-5 text-sm text-slate-600">Una cuenta por DNI. Al registrarte, te enviaremos un código al correo para que lo escribas y verifiques tu cuenta antes de comprar.</p>
    @include('partials.form-errors')
    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <label class="block" for="name">Nombres
            <input id="name" name="name" value="{{ old('name') }}" autocomplete="given-name" required maxlength="100" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <label class="block" for="apellidos">Apellidos
            <input id="apellidos" name="apellidos" value="{{ old('apellidos') }}" autocomplete="family-name" required maxlength="150" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <label class="block" for="dni">DNI
            <input id="dni" name="dni" type="text" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" value="{{ old('dni') }}" required aria-describedby="dni-ayuda" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <p id="dni-ayuda" class="text-sm text-slate-500">Ingresa tus 8 dígitos. Si ya tienes una cuenta, <a href="{{ route('password.request') }}" class="text-blue-700 underline">recupera tu acceso</a>.</p>
        <label class="block" for="email">Correo
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <div class="space-y-2">
            <label for="captcha" class="block">Código de seguridad</label>
            <img id="captcha-image" src="{{ $captcha['image'] }}" width="235" height="72" alt="Código visual de seis dígitos que debes escribir en el campo siguiente" class="rounded-lg border border-slate-300">
            <input id="captcha-id" type="hidden" name="captcha_id" value="{{ $captcha['id'] }}">
            <input id="captcha" name="captcha" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="off" required aria-describedby="captcha-ayuda" class="w-full rounded-lg border border-slate-300 p-3">
            <p id="captcha-ayuda" class="text-sm text-slate-500">Escribe los 6 dígitos de la imagen. El código vence en 10 minutos. <a href="{{ route('register') }}" data-captcha-refresh class="text-blue-700 underline">Cargar otro código</a>.</p>
            <p id="captcha-status" role="status" class="text-sm text-slate-600"></p>
            <p class="text-sm text-slate-500">Si necesitas ayuda para completar el registro, <a href="{{ route('contacto') }}" class="text-blue-700 underline">contacta al negocio</a>.</p>
        </div>
        <div hidden aria-hidden="true">
            <label for="website">Sitio web</label>
            <input id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <label for="privacy_accepted" class="flex items-start gap-3 text-sm">
            <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" @checked(old('privacy_accepted')) required class="mt-1">
            <span>He leído el <a href="{{ route('privacidad') }}" target="_blank" rel="noopener" class="text-blue-700 underline">aviso de privacidad</a> y acepto el uso de mis datos, incluido el DNI, para gestionar mi cuenta y evitar registros duplicados.</span>
        </label>
        <label class="block" for="password">Contraseña
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required aria-describedby="regla-password" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <p id="regla-password" class="text-sm text-slate-500">Al menos 10 caracteres, con letras y números.</p>
        <label class="block" for="password_confirmation">Repite la contraseña
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <button class="min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white hover:bg-blue-700">Crear cuenta</button>
    </form>
    <p class="mt-5 text-sm">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-blue-700 underline">Inicia sesión</a></p>
    <p class="mt-2 text-sm"><a href="{{ route('password.request') }}" class="text-blue-700 underline">Recuperar mi cuenta</a></p>
</div>
@endsection
