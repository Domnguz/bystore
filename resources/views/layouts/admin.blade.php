@extends('adminlte::page')

{{-- Load the shared theme even when a page defines its own CSS section. --}}
@section('adminlte_css')
    @vite('resources/css/admin.css')
    @stack('css')
    @yield('css')
@stop

@section('usermenu_header')
    <li class="bs-admin-user-summary">
        <span class="bs-admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <div><strong>{{ auth()->user()->name }}</strong><span>Administrador de ByteStore</span></div>
    </li>
@stop
