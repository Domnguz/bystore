<div class="row">
    @forelse($pedidos as $pedido)
        <div class="col-xl-6 mb-3">
            <article class="card h-100 mb-0 {{ $pedido->estado === 'Pendiente' && $pedido->comprobante_pago ? 'card-outline card-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:8px">
                        <a href="{{ route('pedidos.show', $pedido->id) }}" class="h5 mb-0">Pedido #{{ $pedido->id }}</a>
                        <span class="badge {{ $pedido->estado === 'Pendiente' && $pedido->comprobante_pago ? 'badge-warning' : ($pedido->estado === 'Pendiente' ? 'badge-light' : ($pedido->estado === 'Rechazado' ? 'badge-secondary' : 'badge-success')) }} p-2">{{ $pedido->estado_visible }}</span>
                    </div>
                    <p class="font-weight-bold mb-1" style="overflow-wrap:anywhere">{{ $pedido->usuario?->name ?? 'Cliente' }}</p>
                    <p class="text-muted small mb-3">{{ $pedido->created_at?->timezone('America/Lima')->format('d/m/Y H:i') }} · {{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en tienda' : 'Envío a domicilio' }}</p>
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
                        <div><small class="text-muted">{{ $pedido->entrega_cotizada ? 'Total del pedido' : 'Subtotal · envío por cotizar' }}</small><div class="h5 font-weight-bold mb-0">S/ {{ number_format($pedido->total, 2) }}</div></div>
                        <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn {{ $pedido->estado === 'Pendiente' && $pedido->comprobante_pago ? 'btn-warning' : 'btn-outline-primary' }}">{{ $pedido->estado === 'Pendiente' && $pedido->comprobante_pago ? 'Revisar pago' : 'Gestionar pedido' }}</a>
                    </div>
                    @if($pedido->estado === 'Pendiente' && $pedido->comprobante_pago)
                        <div class="mt-3 pt-3 border-top text-muted small" style="overflow-wrap:anywhere">Constancia recibida {{ $pedido->comprobante_enviado_at?->timezone('America/Lima')->format('d/m/Y H:i') }} · {{ \App\Services\MetodosPago::nombre($pedido->metodo_pago) }} @if($pedido->pagador_nombre) · {{ $pedido->pagador_nombre }} @endif</div>
                    @endif
                </div>
            </article>
        </div>
    @empty
        @if(request()->filled('buscar') || request()->filled('estado'))
            <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">No hay pedidos que coincidan con estos filtros.</div></div></div>
        @else
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">Aún no hay pedidos.</div></div></div>
        @endif
    @endforelse
</div>
<div class="overflow-auto">{{ $pedidos->links('pagination::bootstrap-4') }}</div>
