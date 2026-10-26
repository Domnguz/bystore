@php
    $footerWhatsApp = \App\Services\WhatsApp::enlace();
@endphp

<footer class="bs-footer">
    <div class="bs-footer-container">
        <div class="bs-footer-benefits" aria-label="Información sobre tu compra">
            <section class="bs-footer-benefit">
                <svg class="bs-footer-benefit-icon" width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="5" width="34" height="38" rx="3" /><rect x="13" y="11" width="8" height="8" /><rect x="27" y="11" width="8" height="8" /><rect x="13" y="25" width="8" height="8" /><path d="M27 25h4v4h4M27 35v-6M35 25v-2M13 38h22" stroke="var(--color-blue-400)" /></svg>
                <h2>Métodos de pago</h2>
                <p>Adjunta la constancia en tu pedido.</p>
                <a href="{{ route('contacto') }}#metodos-pago">Cómo pagar</a>
            </section>
            <section class="bs-footer-benefit">
                <svg class="bs-footer-benefit-icon" width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 13h34v29H7Z" /><rect x="5" y="6" width="38" height="7" rx="1" /><path d="M22 6h5v15l-2.5-2-2.5 2Z" fill="var(--color-blue-400)" stroke="var(--color-blue-400)" /><rect x="12" y="30" width="13" height="7" /><path d="M16 33h5" /></svg>
                <h2>Envío y retiro</h2>
                <p>Coordina cómo recibir tu compra.</p>
                <a href="{{ route('contacto') }}#entregas">Opciones de entrega</a>
            </section>
            <section class="bs-footer-benefit">
                <svg class="bs-footer-benefit-icon" width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 28v-7a15 15 0 0 1 30 0v7M39 34v4a4 4 0 0 1-4 4h-9" /><rect x="6" y="23" width="8" height="13" rx="3" /><rect x="34" y="23" width="8" height="13" rx="3" /><path d="m19 24 4 4 7-8" stroke="var(--color-blue-400)" stroke-width="3" /></svg>
                <h2>Ayuda con tu compra</h2>
                <p>Resuelve tus dudas con la tienda.</p>
                <a href="{{ $footerWhatsApp ?: route('contacto') }}" @if($footerWhatsApp) target="_blank" rel="noopener noreferrer" @endif>Escríbenos @if($footerWhatsApp)<span class="sr-only">(abre otra pestaña)</span>@endif</a>
            </section>
        </div>
    </div>

    <div class="bs-footer-bottom">
        <div class="bs-footer-container">
            <nav class="bs-footer-bottom-links" aria-label="Enlaces del pie de página">
                <a href="{{ route('contacto') }}">Ayuda</a>
                <a href="{{ route('privacidad') }}">Privacidad</a>
                <a href="{{ route('reclamaciones.create') }}">Libro de Reclamaciones</a>
            </nav>
            <p>© {{ now()->timezone('America/Lima')->format('Y') }} ByteStore. Todos los derechos reservados.</p>
            @if(config('tienda.razon_social') || config('tienda.ruc'))
                <p>{{ config('tienda.razon_social') }}@if(config('tienda.ruc')) · RUC: {{ config('tienda.ruc') }}@endif</p>
            @endif
            @if(config('tienda.retiro.direccion'))<p>{{ config('tienda.retiro.direccion') }}</p>@endif
        </div>
    </div>
</footer>
