@extends('layouts.admin')
@section('title', 'Reclamaciones')
@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Libro de Reclamaciones', 'descripcion' => 'Consulta las solicitudes de tus clientes y registra su atención.'])
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="alert alert-info">La respuesta debe entregarse por escrito al consumidor dentro del plazo aplicable. Este panel conserva la respuesta y su referencia de entrega; no envía una carta ni un correo automáticamente.</div>
@forelse($reclamaciones as $hoja)
<div class="card"><div class="card-body">
    <h2 class="h5">Hoja #{{ $hoja->id }} · {{ $hoja->tipo }} · {{ $hoja->respondido_at ? 'Respondida' : 'Pendiente' }}</h2>
    <p>{{ $hoja->created_at->timezone('America/Lima')->format('d/m/Y H:i') }} · {{ $hoja->nombre }} · {{ $hoja->email }} · {{ $hoja->telefono }}</p>
    <a href="{{ route('reclamaciones.show', $hoja->token) }}" target="_blank" rel="noopener noreferrer">Ver hoja completa y copia imprimible</a>
    <p class="mt-3">{{ $hoja->detalle }}</p><p><strong>Solicita:</strong> {{ $hoja->solicitud }}</p>
    <form action="{{ route('admin.reclamaciones.responder', $hoja->id) }}" method="POST">
        @csrf
        <label for="respuesta-{{ $hoja->id }}">Respuesta y acciones adoptadas</label><textarea id="respuesta-{{ $hoja->id }}" name="respuesta" rows="4" required maxlength="10000" class="form-control">{{ $hoja->respuesta }}</textarea>
        <label for="medio-{{ $hoja->id }}" class="mt-3">Medio de entrega</label><select id="medio-{{ $hoja->id }}" name="medio_respuesta" class="form-control"><option>Correo electrónico</option><option>Carta</option></select>
        <label for="evidencia-{{ $hoja->id }}" class="mt-3">Referencia de correo enviado o constancia de entrega</label><input id="evidencia-{{ $hoja->id }}" name="evidencia_respuesta" maxlength="255" required value="{{ $hoja->evidencia_respuesta }}" class="form-control">
        <label class="mt-3"><input type="checkbox" name="respuesta_entregada" value="1" required> Ya entregué la respuesta escrita y conservé la evidencia.</label>
        <button class="btn btn-primary d-block">Registrar respuesta entregada</button>
    </form>
</div></div>
@empty<p>No hay reclamaciones registradas.</p>@endforelse
{{ $reclamaciones->links('pagination::bootstrap-4') }}
@stop

