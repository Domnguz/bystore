<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarritoController extends Controller
{
    public function index(Request $request, CarritoService $service)
    {
        return view('carrito', ['carrito' => $service->sincronizar($request)]);
    }

    public function contenido(Request $request, CarritoService $service)
    {
        $service->sincronizar($request);

        return view('partials.carrito-contenido');
    }

    public function agregar(Request $request, int $id)
    {
        $producto = Producto::findOrFail($id);
        $carrito = $request->session()->get('carrito', []);
        $cantidad = ($carrito[$id]['cantidad'] ?? 0) + 1;
        if ($cantidad > min($producto->stock, 99)) {
            throw ValidationException::withMessages(['cantidad' => 'No hay más unidades disponibles de este producto.']);
        }
        $carrito[$id] = ['id' => $producto->id, 'nombre' => $producto->nombre,
            'precio' => $producto->precio, 'imagen' => $producto->imagen,
            'cantidad' => $cantidad, 'stock' => $producto->stock];
        $request->session()->put('carrito', $carrito);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto agregado al carrito',
                'cantidad' => collect($carrito)->sum('cantidad')]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request, int $id)
    {
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:99']]);
        $producto = Producto::findOrFail($id);
        if ($datos['cantidad'] > $producto->stock) {
            throw ValidationException::withMessages(['cantidad' => 'La cantidad supera las unidades disponibles.']);
        }
        $carrito = $request->session()->get('carrito', []);
        abort_unless(isset($carrito[$id]), 404);
        $carrito[$id]['cantidad'] = (int) $datos['cantidad'];
        $carrito[$id]['precio'] = $producto->precio;
        $request->session()->put('carrito', $carrito);

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function eliminar(Request $request, int $id)
    {
        $carrito = $request->session()->get('carrito', []);
        unset($carrito[$id]);
        $request->session()->put('carrito', $carrito);

        return back()->with('success', 'Producto retirado del carrito.');
    }
}
