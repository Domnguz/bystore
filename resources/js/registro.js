function iniciarCaptcha() {
    const enlace = document.querySelector('[data-captcha-refresh]');
    if (!enlace) return;
    let cargando = false;
    enlace.addEventListener('click', async (event) => {
        event.preventDefault();
        if (cargando) return;
        cargando = true;
        enlace.setAttribute('aria-disabled', 'true');
        const estado = document.getElementById('captcha-status');
        estado.textContent = 'Cargando otro código…';
        try {
            const respuesta = await fetch(enlace.href, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (!respuesta.ok) throw new Error('No se pudo cargar el código');
            const desafio = await respuesta.json();
            document.getElementById('captcha-image').src = desafio.image;
            document.getElementById('captcha-id').value = desafio.id;
            const campo = document.getElementById('captcha');
            campo.value = '';
            campo.focus();
            estado.textContent = 'Nuevo código listo.';
        } catch {
            estado.textContent = 'No pudimos cargar otro código. Espera un minuto e inténtalo de nuevo.';
        } finally {
            cargando = false;
            enlace.removeAttribute('aria-disabled');
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarCaptcha, { once: true });
} else {
    iniciarCaptcha();
}
