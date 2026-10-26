@extends('layouts.app')
@section('title', 'Iniciar sesión | ByteStore')
@section('contenido')
<div class="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
    <h1 class="mb-6 text-2xl font-bold">Bienvenido de nuevo</h1>
    @php
        $mensajeLimite = collect($errors->all())->first(fn ($error) => str_contains($error, 'límite de intentos') || str_contains($error, 'varios intentos'));
    @endphp
    @if($mensajeLimite)
        <div role="alert" class="mb-5 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z"/></svg>
            </span>
            <p class="font-medium">{{ $mensajeLimite }}</p>
        </div>
    @else
        @include('partials.form-errors')
    @endif
    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <label for="email" class="block">Correo
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <label for="password" class="block">Contraseña
            <input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
        </label>
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember" value="1"> Recordarme</label>
            <a href="{{ route('password.request') }}" class="text-blue-700 underline">¿Olvidaste tu contraseña?</a>
        </div>
        <button class="min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white hover:bg-blue-700">Iniciar sesión</button>
    </form>
    <p class="mt-5 text-sm">¿No tienes cuenta? <a href="{{ route('register') }}" class="text-blue-700 underline">Regístrate aquí</a></p>
</div>
@endsection
