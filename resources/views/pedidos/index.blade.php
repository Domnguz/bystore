@extends('layouts.app')
@section('title', 'Mis pedidos | ByteStore')
@section('contenido')
<h1 class="mb-6 text-3xl font-bold">Mis pedidos</h1>
<div class="space-y-4">
@forelse($pedidos as $pedido)
    <a href="{{ route('mis-pedidos.show', $pedido) }}" class="block rounded-xl border border-slate-200 bg-white p-5 hover:border-blue-500">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <strong>Pedido #{{ $pedido->id }}</strong>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-800">{{ $pedido->estado_visible }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-600">{{ $pedido->created_at?->timezone('America/Lima')->format('d/m/Y H:i') }} · {{ $pedido->entrega_cotizada ? 'Total' : 'Productos, envío por cotizar' }}: S/ {{ number_format($pedido->total, 2) }}</p>
        <span class="mt-3 inline-block text-sm font-semibold text-blue-700">{{ $pedido->estado === 'Pendiente' && !$pedido->comprobante_pago && $pedido->entrega_cotizada ? 'Elegir método de pago' : ($pedido->estado === 'Pendiente' && $pedido->comprobante_pago ? 'Consultar revisión del pago' : 'Ver detalle') }} →</span>
    </a>
@empty
    <p class="rounded-xl bg-white p-6">Aún no tienes pedidos. <a href="{{ route('catalogo') }}" class="text-blue-700 underline">Explorar productos</a></p>
@endforelse
</div>
<div class="mt-6">{{ $pedidos->links() }}</div>
@endsection
