@extends('adminlte::page')

@section('title', 'Editar producto | ByteStore')

@section('content_header')
    <h1>Editar producto</h1>
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