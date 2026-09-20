@extends('adminlte::page')

@section('title', 'Nuevo producto | ByteStore')

@section('content_header')
    <h1>Nuevo producto</h1>
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