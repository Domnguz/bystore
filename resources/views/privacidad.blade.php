@extends('layouts.app')
@section('title', 'Privacidad | ByteStore')
@section('contenido')
<article class="mx-auto max-w-3xl space-y-5 leading-7">
    <h1 class="text-3xl font-bold">Información de privacidad</h1>
    <p>ByteStore utiliza los datos de cuenta para permitir el acceso y la recuperación de contraseña; los datos de pedido para gestionar pago, comprobantes y entrega; y los datos de reclamación para atender y conservar la solicitud.</p>
    <h2 class="text-xl font-bold">Datos que proporcionas</h2>
    <p>Incluyen nombre, correo, teléfono, dirección de entrega y, cuando corresponde al comprobante de venta, documento y razón social. Si pagas mediante Yape, Plin o transferencia bancaria, puedes enviar una constancia de la operación. No solicitamos contraseñas ni códigos de autorización.</p>
    <h2 class="text-xl font-bold">DNI y verificación de la cuenta</h2>
    <p>Para registrar una cuenta y realizar pedidos solicitamos un DNI de 8 dígitos. Lo utilizamos para evitar que el mismo documento se asocie a varias cuentas. No pedimos una foto del DNI ni realizamos consultas a RENIEC. El número declarado no acredita por sí solo la identidad de quien lo ingresa.</p>
    <p>Solicitamos tu aceptación de este uso en el registro y cuando completas el DNI de una cuenta antigua, y guardamos la fecha de aceptación. Conservamos el DNI en el registro privado de tu cuenta mientras la cuenta exista; los administradores autorizados pueden consultarlo para gestionar incidencias. No se muestra en el catálogo ni se incluye en enlaces de verificación o recuperación.</p>
    <p>La verificación por correo comprueba que controlas la dirección indicada. Si cambias el correo, deberás verificar la nueva dirección antes de crear pedidos. El registro utiliza un código visual de seguridad, una sesión temporal y límites de intentos para reducir registros automáticos; ese código no se envía a un proveedor externo.</p>
    <p>Si tu DNI figura en una cuenta que no reconoces, o necesitas corregirlo, contacta al negocio. No compartas tu contraseña ni códigos de acceso. La atención de solicitudes de corrección o eliminación requiere revisar el caso y los registros que deban conservarse para gestionar pedidos y comprobantes.</p>
    <h2 class="text-xl font-bold">Acceso y almacenamiento</h2>
    <p>Los pedidos y sus constancias se consultan desde la cuenta del comprador y por administradores autorizados. Las imágenes públicas de productos se utilizan para presentar el catálogo. La sesión del navegador permite mantener el acceso y el carrito.</p>
    <h2 class="text-xl font-bold">Coordinación por WhatsApp</h2>
    <p>Si eliges coordinar por WhatsApp, se abre ese servicio con un mensaje preparado que incluye el número de pedido y los productos. Puedes revisarlo antes de enviarlo. La dirección y otros datos que compartas en la conversación se usarán para coordinar la entrega.</p>
    <h2 class="text-xl font-bold">Ubicación opcional</h2>
    <p>El selector de ubicación se utiliza cuando pulsas el botón y autorizas el acceso en tu navegador. Envía coordenadas a BigDataCloud para identificar la ciudad y el país. El nombre de la ubicación se conserva temporalmente en la pestaña; no sustituye la dirección de entrega que ingresas en el pedido.</p>
    <h2 class="text-xl font-bold">Consultas sobre tus datos</h2>
    @if(config('tienda.contacto'))<p>Contacta al negocio en {{ config('tienda.contacto') }} para consultas sobre tus datos y el ejercicio de tus derechos.</p>@else<p>Puedes presentar tu solicitud mediante el <a href="{{ route('reclamaciones.create') }}" class="text-blue-700 underline">formulario de atención</a>. Indica tu correo para recibir respuesta.</p>@endif
    <p class="text-sm text-slate-500">{{ config('tienda.razon_social') ?: 'ByteStore' }}@if(config('tienda.ruc')) · RUC {{ config('tienda.ruc') }}@endif</p>
</article>
@endsection
