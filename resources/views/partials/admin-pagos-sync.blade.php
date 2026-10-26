<script>
(() => {
    const listado = document.getElementById('listaPedidosAdmin');
    const alerta = document.getElementById('alertaPagosAdmin');
    const url = new URL(@json(route('pedidos.actualizaciones')));
    if (listado) {
        const pagina = new URL(window.location.href);
        url.searchParams.set('page', pagina.searchParams.get('page') || '1');
        ['buscar', 'estado'].forEach(filtro => {
            if (pagina.searchParams.has(filtro)) url.searchParams.set(filtro, pagina.searchParams.get(filtro));
        });
    }
    else url.searchParams.set('resumen', '1');
    let revision = listado?.dataset.revision;
    let consultando = false;
    let activo = true;
    async function actualizar() {
        if (!activo || consultando || document.hidden) return;
        consultando = true;
        const controlador = new AbortController();
        const limite = setTimeout(() => controlador.abort(), 8000);
        try {
            const respuesta = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store', credentials: 'same-origin', signal: controlador.signal });
            if (respuesta.status === 401 || respuesta.status === 403) { activo = false; return; }
            if (!respuesta.ok || respuesta.redirected) return;
            const datos = await respuesta.json();
            document.querySelectorAll('[data-pagos-pendientes]').forEach(elemento => { elemento.textContent = datos.pagosPendientes; });
            document.querySelectorAll('[data-cantidad-resultados]').forEach(elemento => { elemento.textContent = datos.cantidadResultados; });
            document.querySelectorAll('[data-total-pedidos]').forEach(elemento => { elemento.textContent = datos.totalPedidos; });
            if (alerta) alerta.hidden = datos.pagosPendientes === 0;
            if (listado && revision !== datos.revision) {
                const anterior = document.activeElement?.closest('#listaPedidosAdmin a');
                const hrefAnterior = anterior?.getAttribute('href');
                listado.innerHTML = datos.lista;
                revision = datos.revision;
                document.querySelectorAll('[data-pedido-count]').forEach(elemento => { elemento.textContent = datos.resumen[elemento.dataset.pedidoCount] || 0; });
                if (hrefAnterior) [...listado.querySelectorAll('a')].find(enlace => enlace.getAttribute('href') === hrefAnterior)?.focus({ preventScroll: true });
                document.getElementById('estadoPagosAdmin').textContent = 'Lista actualizada con los últimos pedidos y pagos.';
            }
        } catch { /* Una conexión interrumpida se reintenta en la siguiente consulta. */ }
        finally { clearTimeout(limite); consultando = false; }
    }
    const intervalo = setInterval(actualizar, 15000);
    document.addEventListener('visibilitychange', actualizar);
    window.addEventListener('pagehide', () => { activo = false; clearInterval(intervalo); });
    window.addEventListener('pageshow', evento => { if (evento.persisted) window.location.reload(); });
})();
</script>
