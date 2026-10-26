<?php

namespace App\Services;

class Igv
{
    public static function desglosar(string|int $importe, string|int|float $tasa = 18): array
    {
        $totalCentimos = Dinero::centimos($importe);
        $tasaPuntosBase = (int) round((float) $tasa * 100);
        $factor = 10_000 + $tasaPuntosBase;
        $baseCentimos = intdiv(($totalCentimos * 10_000) + intdiv($factor, 2), $factor);
        $igvCentimos = $totalCentimos - $baseCentimos;

        return [
            'base_imponible' => Dinero::decimal($baseCentimos),
            'igv' => Dinero::decimal($igvCentimos),
            'total' => Dinero::decimal($totalCentimos),
            'tasa' => number_format($tasa, 2, '.', ''),
        ];
    }
}
