<section id="pago-pedido" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
        <div><p class="text-xs font-bold uppercase tracking-widest text-purple-700">Pago del pedido</p><h2 class="mt-1 text-xl font-bold">{{ $pedido->comprobante_pago ? 'Tu constancia de pago' : 'Paga tu pedido' }}</h2></div>
        <span class="rounded-xl bg-purple-100 px-3 py-2 text-sm font-bold text-purple-800">{{ \App\Services\MetodosPago::nombre($pedido->metodo_pago) }}</span>
    </div>
    <div class="p-5 sm:p-6">
        @if($pedido->estado === 'Pendiente')
            @if($pedido->comprobante_pago)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4" role="status">
                    <h3 class="font-bold text-amber-900">Pago en revisión</h3>
                    <p class="mt-2 text-sm leading-6 text-amber-800">Tu constancia ya está en el panel del administrador. Revisaremos el ingreso de {{ \App\Services\MetodosPago::nombre($pedido->metodo_pago) }} y confirmaremos tu pago aquí.</p>
                </div>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Total del pedido</dt><dd class="mt-1 font-bold">S/ {{ number_format($pedido->total, 2) }}</dd></div>
                    <div><dt class="text-slate-500">Método de pago</dt><dd class="mt-1 break-words font-medium">{{ \App\Services\MetodosPago::nombre($pedido->metodo_pago) }}</dd></div>
                    <div><dt class="text-slate-500">Nombre del pagador</dt><dd class="mt-1 break-words font-medium">{{ $pedido->pagador_nombre ?: 'No indicado' }}</dd></div>
                    <div><dt class="text-slate-500">Fecha del pago</dt><dd class="mt-1 font-medium">{{ $pedido->pago_realizado_at?->timezone('America/Lima')->format('d/m/Y H:i') ?: 'No indicada' }}</dd></div>
                </dl>
                <a href="{{ route('mis-pedidos.comprobante', $pedido) }}" target="_blank" rel="noopener" class="mt-5 flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-blue-700 hover:border-blue-500">Ver mi constancia <span class="sr-only">(abre otra pestaña)</span></a>
            @elseif($pedido->reserva_hasta && $pedido->reserva_hasta->isPast())
                <p class="rounded-xl bg-red-50 p-4 text-sm leading-6 text-red-800">La reserva venció. Contacta a la tienda antes de realizar un pago.</p>
            @elseif(!$pedido->entrega_cotizada)
                <h3 class="font-bold">Estamos coordinando tu envío</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">Primero confirmaremos la tarifa y el total por WhatsApp. Cuando la tienda registre la cotización, aparecerán aquí los métodos disponibles para pagar.</p>
            @elseif(empty($metodosPago = \App\Services\MetodosPago::disponibles()))
                <p class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">Estamos preparando los métodos de pago de la tienda. Contacta con nosotros antes de pagar.</p>
            @else
                @php($metodoInicial = old('metodo_pago', array_key_first($metodosPago)))
                @if(config('tienda.pagos_demo'))
                    <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-900" role="note">Modo demostración: Plin y transferencia muestran datos de ejemplo. El QR de Yape configurado por la tienda se mantiene visible; verifica el titular antes de pagar.</div>
                @endif
                <div class="rounded-2xl border border-purple-100 bg-purple-50 p-4 text-center sm:p-5">
                    <p class="text-xs font-semibold text-purple-700">TOTAL A PAGAR · PEDIDO #{{ $pedido->id }}</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-purple-950">S/ {{ number_format($pedido->total, 2) }}</p>
                    <label for="metodo_pago" class="mt-5 block text-left text-sm font-semibold text-slate-700">Elige cómo pagar</label>
                    <select id="metodo_pago" name="metodo_pago" form="formulario-constancia-pago" required class="mt-2 min-h-12 w-full rounded-xl border border-purple-200 bg-white px-3 text-base" data-payment-method>
                        @foreach($metodosPago as $codigo => $nombre)<option value="{{ $codigo }}" @selected($metodoInicial === $codigo)>{{ $nombre }}</option>@endforeach
                    </select>
                    <div class="mt-4 rounded-xl bg-white p-4" data-payment-method-details="yape" @if($metodoInicial !== 'yape') hidden @endif>
                    @if($qr = \App\Services\PagoYape::qrUrl())
                        <div class="mx-auto mt-5 w-full max-w-64 rounded-2xl border border-purple-100 bg-white p-3 shadow-sm">
                            <img src="{{ $qr }}" alt="Escanea este QR en Yape para pagar a {{ config('tienda.yape.titular') }}" width="438" height="422" class="h-auto w-full object-contain">
                        </div>
                        <a href="{{ $qr }}" download="QR-Yape-ByteStore" class="mt-3 inline-flex min-h-11 items-center justify-center gap-2 text-sm font-semibold text-purple-700 hover:underline">Descargar QR <span aria-hidden="true">↓</span></a>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Si estás en tu celular, descarga el QR y ábrelo desde la opción de imágenes de Yape.</p>
                    @endif
                    <p class="mt-4 text-sm text-slate-600">Titular: <strong class="break-words text-slate-900">{{ config('tienda.yape.titular') }}</strong></p>
                    @if(config('tienda.yape.numero'))<p class="mt-2 text-sm text-slate-600">También puedes yapear al <strong class="text-slate-900">{{ config('tienda.yape.numero') }}</strong></p>@endif
                    <p class="mt-3 text-xs leading-5 text-slate-500">Comprueba que el titular y el importe sean correctos antes de pagar con Yape.</p>
                    </div>
                    @if(isset($metodosPago['plin']))
                        <div class="mt-4 rounded-xl bg-white p-4 text-left" data-payment-method-details="plin" @if($metodoInicial !== 'plin') hidden @endif>
                            <h3 class="font-bold text-slate-900">Paga con Plin</h3><p class="mt-2 text-sm text-slate-600">Titular: <strong class="text-slate-900">{{ config('tienda.plin.titular') }}</strong></p><p class="mt-1 text-sm text-slate-600">Número: <strong class="text-slate-900">{{ config('tienda.plin.numero') }}</strong></p><p class="mt-3 text-xs text-slate-500">Envía exactamente S/ {{ number_format($pedido->total, 2) }}.</p>
                        </div>
                    @endif
                    @if(isset($metodosPago['transferencia']))
                        <div class="mt-4 rounded-xl bg-white p-4 text-left" data-payment-method-details="transferencia" @if($metodoInicial !== 'transferencia') hidden @endif>
                            <h3 class="font-bold text-slate-900">Datos para transferir</h3><p class="mt-2 text-sm text-slate-600">Banco: <strong class="text-slate-900">{{ config('tienda.transferencia.banco') }}</strong></p><p class="mt-1 text-sm text-slate-600">Titular: <strong class="text-slate-900">{{ config('tienda.transferencia.titular') }}</strong></p>
                            @if(config('tienda.transferencia.cuenta'))<p class="mt-1 break-all text-sm text-slate-600">Cuenta: <strong class="text-slate-900">{{ config('tienda.transferencia.cuenta') }}</strong></p>@endif
                            @if(config('tienda.transferencia.cci'))<p class="mt-1 break-all text-sm text-slate-600">CCI: <strong class="text-slate-900">{{ config('tienda.transferencia.cci') }}</strong></p>@endif
                            <p class="mt-3 text-xs text-slate-500">Transfiere exactamente S/ {{ number_format($pedido->total, 2) }}.</p>
                        </div>
                    @endif
                </div>

                <form id="formulario-constancia-pago" action="{{ route('mis-pedidos.pago', $pedido) }}" method="POST" enctype="multipart/form-data" class="mt-6 min-w-0 space-y-4" data-payment-form>
                    @csrf
                    <input type="hidden" name="total_confirmado" value="{{ $pedido->total }}">
                    <div><h3 class="text-lg font-bold">¿Ya realizaste el pago?</h3><p class="mt-1 text-sm leading-6 text-slate-500">Envía estos datos y la constancia para que el administrador identifique tu pago.</p></div>
                    <label for="pagador_nombre" class="block text-sm font-medium">Nombre que aparece en la cuenta de pago
                        <input id="pagador_nombre" name="pagador_nombre" required maxlength="150" autocomplete="name" value="{{ old('pagador_nombre', auth()->user()->name) }}" class="mt-2 min-h-12 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-50 px-3 text-base focus:border-purple-500 focus:outline-purple-500">
                    </label>
                    <div class="grid min-w-0 gap-4">
                        <label for="pago_realizado_at" class="block min-w-0 text-sm font-medium">Fecha y hora del pago
                            <input id="pago_realizado_at" name="pago_realizado_at" type="datetime-local" required value="{{ old('pago_realizado_at', now()->timezone('America/Lima')->format('Y-m-d\TH:i')) }}" max="{{ now()->timezone('America/Lima')->format('Y-m-d\TH:i') }}" class="mt-2 min-h-12 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-50 px-3 text-base focus:border-purple-500 focus:outline-purple-500">
                        </label>
                    </div>
                    <div class="min-w-0 rounded-xl border border-dashed border-purple-200 bg-purple-50 p-4">
                        <label for="comprobante" class="block text-sm font-semibold text-purple-900">Adjunta la captura de tu pago</label>
                        <p id="ayudaConstancia" class="mt-1 text-xs leading-5 text-slate-500">JPG, PNG o WebP · máximo 5 MB. Deben verse el nombre, el monto y la fecha.</p>
                        <input id="comprobante" type="file" name="comprobante" accept="image/jpeg,image/png,image/webp" required aria-describedby="ayudaConstancia" class="mt-3 block w-full min-w-0 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-3 file:font-semibold file:text-purple-700" data-payment-file>
                        <img alt="Vista previa de tu constancia" class="mx-auto mt-4 max-h-64 max-w-full rounded-lg object-contain" hidden data-payment-preview>
                    </div>
                    <fieldset class="min-w-0 space-y-4 rounded-xl border border-slate-200 p-4">
                        <legend class="px-1 text-base font-bold">Comprobante de venta</legend>
                        <p class="text-sm leading-6 text-slate-500">Completa estos datos al enviar tu constancia para que la tienda emita tu boleta o factura.</p>
                        <label for="tipo_comprobante" class="block text-sm font-medium">Tipo
                            <select id="tipo_comprobante" name="tipo_comprobante" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-base focus:border-purple-500 focus:outline-purple-500" data-payment-receipt-type>
                                <option value="boleta" @selected(old('tipo_comprobante', $pedido->tipo_comprobante ?? 'boleta') === 'boleta')>Boleta</option>
                                <option value="factura" @selected(old('tipo_comprobante', $pedido->tipo_comprobante) === 'factura')>Factura</option>
                            </select>
                        </label>
                        <label for="documento" class="block text-sm font-medium"><span data-payment-document-label>DNI o RUC (RUC obligatorio para factura)</span>
                            <input id="documento" name="documento" value="{{ old('documento', $pedido->documento) }}" inputmode="numeric" maxlength="11" pattern="(?:[0-9]{8}|[0-9]{11})" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-base focus:border-purple-500 focus:outline-purple-500" data-payment-document>
                        </label>
                        <label for="razon_social" class="block text-sm font-medium" data-payment-business-label>Razón social (para factura)
                            <input id="razon_social" name="razon_social" value="{{ old('razon_social', $pedido->razon_social) }}" maxlength="255" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-base focus:border-purple-500 focus:outline-purple-500" data-payment-business-name>
                        </label>
                    </fieldset>
                    <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-purple-700 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-purple-800 disabled:cursor-wait disabled:opacity-60" data-payment-submit>Ya pagué · Enviar constancia</button>
                    <p class="text-center text-xs leading-5 text-slate-500">La tienda verificará el ingreso. Enviar la constancia deja tu pago en revisión.</p>
                </form>
            @endif
            @if($pedido->reserva_hasta && !$pedido->comprobante_pago)<p class="mt-5 text-xs leading-5 text-slate-500">Reserva hasta {{ $pedido->reserva_hasta->timezone('America/Lima')->format('d/m/Y H:i') }}.</p>@endif
            @if(!$pedido->comprobante_pago)
                <form action="{{ route('mis-pedidos.cancelar', $pedido) }}" method="POST" class="mt-4 border-t border-slate-100 pt-4">
                    @csrf
                    <button class="min-h-11 text-sm font-medium text-slate-500 hover:text-red-700">Cancelar mi pedido</button>
                </form>
            @endif
        @elseif($pedido->estado === 'Rechazado')
            <p class="rounded-xl bg-red-50 p-4 text-sm leading-6 text-red-800">Este pedido está cancelado. No realices un pago.</p>
        @else
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <h3 class="font-bold text-green-900">Pago confirmado</h3>
                <p class="mt-2 text-sm leading-6 text-green-800">La tienda verificó tu pago de S/ {{ number_format($pedido->total, 2) }}. Sigue el avance de tu entrega en este pedido.</p>
                @if($pedido->pagado_at)<p class="mt-3 text-xs text-green-700">Confirmado el {{ $pedido->pagado_at->timezone('America/Lima')->format('d/m/Y H:i') }}.</p>@endif
            </div>
        @endif
        @if(config('tienda.contacto'))<p class="mt-4 break-words text-xs text-slate-500">Contacto: {{ config('tienda.contacto') }}</p>@endif
    </div>
</section>
