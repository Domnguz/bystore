<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoService
{
    public function sincronizar(Request $request): array
    {
        $carrito = $request->session()->get('carrito', []);
        $productos = Producto::whereIn('id', array_keys($carrito))->get()->keyBy('id');
        $actualizado = [];
        foreach ($carrito as $id => $item) {
            if ($producto = $productos->get($id)) {
                $actualizado[$id] = [
                    'id' => $producto->id, 'nombre' => $producto->nombre,
                    'precio' => $producto->precio, 'imagen' => $producto->imagen,
                    'cantidad' => (int) $item['cantidad'], 'stock' => $producto->stock,
                ];
            }
        }
        $request->session()->put('carrito', $actualizado);

        return $actualizado;
    }

    public function huella(array $carrito): string
    {
        ksort($carrito, SORT_NUMERIC);

        return hash('sha256', json_encode(array_map(fn ($item) => [
            'id' => (int) $item['id'], 'cantidad' => (int) $item['cantidad'],
            'precio' => Dinero::centimos((string) $item['precio']),
        ], $carrito), JSON_THROW_ON_ERROR));
    }
}
