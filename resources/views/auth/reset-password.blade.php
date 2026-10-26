@extends('layouts.app')
@section('title', 'Nueva contraseña | ByteStore')
@section('contenido')
<div class="mx-auto max-w-md rounded-2xl bg-white p-6 shadow-sm">
    <h1 class="mb-6 text-2xl font-bold">Elige una nueva contraseña</h1>
    @include('partials.form-errors')
    <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email" class="block">Correo
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <label for="password" class="block">Nueva contraseña
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="10" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <p class="text-sm text-slate-500">Al menos 10 caracteres, con letras y números.</p>
        <label for="password_confirmation" class="block">Repite la contraseña
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <button class="min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white">Actualizar contraseña</button>
    </form>
</div>
@endsection

