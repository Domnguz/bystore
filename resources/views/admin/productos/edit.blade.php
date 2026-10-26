@extends('layouts.admin')

@section('title', 'Editar producto | ByteStore')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Editar producto', 'descripcion' => 'Actualiza la información, las fotos y el stock del producto.'])
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                action="{{ route('productos.update', $producto->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                @include('admin.productos._form')
            </form>
        </div>
    </div>
@stop