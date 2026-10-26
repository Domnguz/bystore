<div class="{{ ($admin ?? false) ? 'alert alert-info' : 'mb-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900' }}" hidden data-order-update>
    El pedido se actualizó. <a href="{{ url()->current() }}" class="{{ ($admin ?? false) ? 'alert-link' : 'font-semibold underline' }}">Revisar los datos actuales</a>
</div>
<script>
(() => {
    const url = @json($syncUrl);
    const revision = @json($pedido->revision_pago);
    const aviso = document.querySelector('[data-order-update]');
    let editando = false;
    let consultando = false;
    let activo = true;
    const escucharFormularios = () => document.querySelectorAll('main form, .content form').forEach(form => {
        form.addEventListener('input', () => { editando = true; });
        form.addEventListener('change', () => { editando = true; });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', escucharFormularios, { once: true });
    else escucharFormularios();
    async function actualizar() {
        if (!activo || consultando || document.hidden) return;
        consultando = true;
        const controlador = new AbortController();
        const limite = setTimeout(() => controlador.abort(), 8000);
        try {
            const respuesta = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: controlador.signal });
            if (respuesta.status === 401 || respuesta.status === 403 || respuesta.status === 404) { activo = false; return; }
            if (!respuesta.ok || respuesta.redirected) return;
            const datos = await respuesta.json();
            if (datos.revision !== revision) {
                activo = false;
                if (editando) {
                    aviso.hidden = false;
                    const botonPago = document.querySelector('[data-payment-submit]');
                    if (botonPago) { botonPago.disabled = true; botonPago.textContent = 'Revisa el pedido actualizado antes de enviar'; }
                }
                else window.location.reload();
            }
        } catch { /* La siguiente consulta reintenta sin interrumpir el formulario. */ }
        finally { clearTimeout(limite); consultando = false; }
    }
    const intervalo = setInterval(actualizar, 15000);
    document.addEventListener('visibilitychange', actualizar);
    window.addEventListener('pagehide', () => { activo = false; clearInterval(intervalo); });
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
})();
</script>
