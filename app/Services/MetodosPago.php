<?php

namespace App\Services;

class MetodosPago
{
    public static function disponibles(): array
    {
        $metodos = [];
        if (PagoYape::configurado()) {
            $metodos['yape'] = 'Yape';
        }
        if (config('tienda.plin.titular') && config('tienda.plin.numero')) {
            $metodos['plin'] = 'Plin';
        }
        if (config('tienda.transferencia.banco') && config('tienda.transferencia.titular')
            && (config('tienda.transferencia.cuenta') || config('tienda.transferencia.cci'))) {
            $metodos['transferencia'] = 'Transferencia bancaria';
        }

        return $metodos;
    }

    public static function nombre(?string $metodo): string
    {
        return self::disponibles()[$metodo ?? ''] ?? match ($metodo) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transferencia' => 'Transferencia bancaria',
            default => 'No indicado',
        };
    }
}
