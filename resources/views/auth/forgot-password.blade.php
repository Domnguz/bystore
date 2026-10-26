@extends('layouts.app')
@section('title', 'Recuperar contraseña | ByteStore')
@section('contenido')
<div class="mx-auto max-w-md rounded-2xl bg-white p-6 shadow-sm">
    <h1 class="mb-3 text-2xl font-bold">Recupera tu acceso</h1>
    <p class="mb-5 text-sm text-slate-600">Te enviaremos un enlace al correo de tu cuenta.</p>
    <p class="mb-5 text-sm text-slate-600">Si tu DNI ya está registrado, recupera tu cuenta original. Si no recuerdas el correo o crees que alguien usó tu DNI, <a href="{{ route('contacto') }}" class="text-blue-700 underline">contacta al negocio</a> para revisar el caso. El DNI por sí solo no permite restablecer la contraseña.</p>
    @include('partials.form-errors')
    <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
        @csrf
        <label for="email" class="block">Correo
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <button class="min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white">Enviar enlace</button>
    </form>
    <a href="{{ route('login') }}" class="mt-5 inline-block text-sm text-blue-700 underline">Volver al inicio de sesión</a>
</div>
@endsection
