<?php

return [
    'retiro' => ['habilitado' => true, 'direccion' => env('TIENDA_DIRECCION', '')],
    'envio' => ['habilitado' => true],
    'contacto' => env('TIENDA_CONTACTO', ''),
    'whatsapp' => env('TIENDA_WHATSAPP', ''),
    'razon_social' => env('TIENDA_RAZON_SOCIAL', ''),
    'ruc' => env('TIENDA_RUC', ''),
    'igv_porcentaje' => 18,
    'pagos_demo' => filter_var(env('TIENDA_PAGOS_DEMO', false), FILTER_VALIDATE_BOOLEAN),
    'yape' => ['numero' => env('YAPE_NUMERO', ''), 'titular' => env('YAPE_TITULAR', ''), 'qr' => env('YAPE_QR', '')],
    'plin' => ['numero' => env('PLIN_NUMERO', ''), 'titular' => env('PLIN_TITULAR', '')],
    'transferencia' => ['banco' => env('TRANSFERENCIA_BANCO', ''), 'titular' => env('TRANSFERENCIA_TITULAR', ''), 'cuenta' => env('TRANSFERENCIA_CUENTA', ''), 'cci' => env('TRANSFERENCIA_CCI', '')],
    'reserva_horas' => 48,
    'stock_bajo_limite' => (int) env('TIENDA_STOCK_BAJO_LIMITE', 5),
    // Yape es manual: la confirmación requiere comprobación en la cuenta del negocio.
];
