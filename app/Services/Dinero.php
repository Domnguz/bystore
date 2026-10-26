<?php

namespace App\Services;

use InvalidArgumentException;

class Dinero
{
    public static function centimos(string|int $importe): int
    {
        $importe = (string) $importe;
        if (! preg_match('/^([0-9]+)(?:\.([0-9]{1,2}))?$/', $importe, $partes)) {
            throw new InvalidArgumentException('El importe debe tener como máximo dos decimales.');
        }

        return ((int) $partes[1] * 100) + (int) str_pad($partes[2] ?? '', 2, '0');
    }

    public static function decimal(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
