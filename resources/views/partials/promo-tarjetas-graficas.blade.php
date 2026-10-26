<section class="gpu-promo col-span-full" aria-labelledby="promo-graficas-titulo">
    <div class="gpu-promo__content">
        <p class="gpu-promo__eyebrow"><span aria-hidden="true"></span> ByteStore <b>·</b> Zona gaming</p>
        <h2 id="promo-graficas-titulo">Ármate con nuestras <span class="gpu-promo__accent">tarjetas gráficas.</span></h2>
        <p class="gpu-promo__description">Elige la GPU ideal para disfrutar mejores gráficos, crear contenido y llevar tu PC al siguiente nivel.</p>

        <div class="gpu-promo__tags" aria-label="Usos de las tarjetas gráficas">
            <span>Gaming</span><span>Creación</span><span>Alto rendimiento</span>
        </div>

        <a href="{{ $categoriaGrafica ? url('/categoria/'.$categoriaGrafica->id) : url('/productos?q=tarjeta+grafica') }}" class="gpu-promo__button">
            Explorar tarjetas gráficas
            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" aria-hidden="true"><path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    <div class="gpu-promo__visual" aria-hidden="true">
        <div class="gpu-promo__orbit gpu-promo__orbit--one"></div>
        <div class="gpu-promo__orbit gpu-promo__orbit--two"></div>
        <span class="gpu-promo__float gpu-promo__float--top">TARJETAS GRÁFICAS</span>
        <div class="gpu-promo__product">
            @if($graficaDestacada?->imagen)
                <img src="{{ asset('storage/'.$graficaDestacada->imagen) }}" alt="" loading="lazy" decoding="async">
            @else
                <svg viewBox="0 0 240 150" fill="none"><path d="M24 39h145l47 36-47 36H24a10 10 0 0 1-10-10V49a10 10 0 0 1 10-10Z" fill="#172554" stroke="#087caa" stroke-width="4"/><path d="M169 39v72M28 113v17h28v-17m88 0v17h28v-17" stroke="#087caa" stroke-width="4" stroke-linejoin="round"/><circle cx="75" cy="75" r="25" fill="#0f172a" stroke="#38bdf8" stroke-width="4"/><circle cx="132" cy="75" r="25" fill="#0f172a" stroke="#38bdf8" stroke-width="4"/><circle cx="75" cy="75" r="7" fill="#38bdf8"/><circle cx="132" cy="75" r="7" fill="#38bdf8"/></svg>
            @endif
        </div>
        <span class="gpu-promo__float gpu-promo__float--bottom"><span></span> Potencia para tu PC</span>
    </div>
</section>
