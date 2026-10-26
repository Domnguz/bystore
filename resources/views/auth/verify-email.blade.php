@extends('layouts.app')
@section('title', 'Verifica tu correo | ByteStore')
@section('contenido')
<div class="mx-auto max-w-md space-y-5 rounded-2xl bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-bold">Verifica tu correo</h1>
    @if ($correoDisponible)
        <p>Escribe el código de 6 dígitos que enviamos a <strong>{{ auth()->user()->email }}</strong> para poder realizar pedidos.</p>
        <p id="codigo-ayuda" class="text-sm text-slate-600">Revisa también la carpeta de spam. El código vence en {{ $minutosValidez }} minutos. Si no lo recibiste, solicita uno nuevo abajo.</p>
    @else
        <p>Tu cuenta está guardada con el correo <strong>{{ auth()->user()->email }}</strong>.</p>
        <p id="codigo-ayuda" role="status" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">El envío de correos aún no está disponible. Contacta a la tienda para completar la verificación.</p>
        <a href="{{ route('contacto') }}" class="inline-flex min-h-11 items-center font-semibold text-blue-700 underline">Contactar a la tienda</a>
    @endif
    @include('partials.form-errors')
    <form action="{{ route('verification.verify') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="codigo" class="mb-2 block text-sm font-semibold">Código de verificación</label>
            <input id="codigo" name="codigo" type="text" inputmode="numeric" autocomplete="one-time-code"
                pattern="[0-9]{6}" minlength="6" maxlength="6" required autofocus
                placeholder="000000" aria-describedby="codigo-ayuda" @error('codigo') aria-invalid="true" @enderror
                class="min-h-14 w-full rounded-lg border border-slate-300 p-3 text-center font-mono text-2xl tracking-[0.4em] focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>
        <button class="min-h-12 w-full rounded-lg bg-blue-600 p-3 font-semibold text-white hover:bg-blue-700">Verificar mi correo</button>
    </form>
    @if ($correoDisponible)
        <form action="{{ route('verification.send') }}" method="POST" class="space-y-2">
            @csrf
            <button class="min-h-12 w-full rounded-lg border border-blue-600 p-3 font-semibold text-blue-700 hover:bg-blue-50">Reenviar código</button>
            <p class="text-xs text-slate-500">Puedes solicitar un código por minuto, hasta 6 por hora. Usa el último código recibido.</p>
        </form>
    @endif
    <a href="{{ route('profile') }}" class="block text-sm text-blue-700 underline">Corregir mi correo en Mi cuenta</a>
    <a href="{{ route('catalogo') }}" class="block text-sm text-blue-700 underline">Seguir viendo productos</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="text-sm text-slate-600 underline">Cerrar sesión</button>
    </form>
</div>
@endsection
