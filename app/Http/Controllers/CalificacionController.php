<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalificacionController extends Controller
{
    public function store(Request $request, int $pedido, int $producto)
    {
        $datos = $request->validate([
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $pedido, $producto, $datos) {
            $orden = Pedido::where('usuario_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($pedido);

            if ($orden->estado !== 'Entregado') {
                throw ValidationException::withMessages([
                    'calificacion' => 'Podrás calificar este producto cuando el pedido figure como entregado.',
                ]);
            }

            $productoComprado = $orden->detalles()
                ->where('producto_id', $producto)
                ->exists();

            if (! $productoComprado || ! Producto::whereKey($producto)->exists()) {
                abort(404);
            }

            Resena::updateOrCreate(
                ['usuario_id' => $request->user()->id, 'producto_id' => $producto],
                [
                    'pedido_id' => $orden->id,
                    'calificacion' => $datos['calificacion'],
                    'comentario' => filled($datos['comentario'] ?? null) ? trim($datos['comentario']) : null,
                ]
            );
        }, 3);

        return back()->with('success', '¡Gracias! Guardamos tu calificación del producto.');
    }
}
