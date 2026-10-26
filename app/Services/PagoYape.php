<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PagoYape
{
    public static function qrUrl(): ?string
    {
        $ruta = config('tienda.yape.qr');
        if (is_string($ruta) && str_starts_with($ruta, 'pagos/yape/') && Storage::disk('public')->exists($ruta)) {
            return asset('storage/'.$ruta);
        }

        return is_file(public_path('img/Yape.png')) ? asset('img/Yape.png') : null;
    }

    public static function configurado(): bool
    {
        return (bool) config('tienda.yape.titular') && (config('tienda.yape.numero') || self::qrUrl());
    }
}
