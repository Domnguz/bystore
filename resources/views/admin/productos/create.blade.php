@extends('layouts.admin')

@section('title', 'Nuevo producto | ByteStore')

@section('content_header')
    @include('partials.admin-heading', ['titulo' => 'Nuevo producto', 'descripcion' => 'Agrega la información y las fotos del producto a tu catálogo.'])
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                action="{{ route('productos.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                @include('admin.productos._form')
            </form>
        </div>
    </div>
@stop