<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\Banner;

class ProductoController extends Controller
{
public function index()
{
    $productos = Producto::with('categoria')
        ->orderBy('id')
        ->paginate(12);

    $categorias = Categoria::all();

    // Solo muestra banners activos, en el orden elegido por el administrador.
    $banners = Banner::where('activo', true)
        ->orderBy('orden')
        ->orderBy('id')
        ->get();

    return view(
        'productos',
        compact('productos', 'categorias', 'banners')
    );
}
    
// Busca el producto con su categoría y sus fotos adicionales.
public function show($id)
{
    $producto = Producto::with([
        'categoria',
        'imagenes',
    ])->findOrFail($id);

    // Muestra resources/views/productos/show.blade.php.
    return view('productos.show', compact('producto'));
}





    public function categoria($id)
{
    // Busca la categoría seleccionada.
    // Si no existe, Laravel muestra un error 404.
    $categoria = Categoria::findOrFail($id);

    // Obtiene solamente sus productos, de 12 en 12.
    $productos = Producto::where('categoria_id', $categoria->id)
        ->orderBy('id')
        ->paginate(12);

    // Categorías disponibles para la navegación superior.
    $categorias = Categoria::all();

    return view(
        'categoria',
        compact('categoria', 'productos', 'categorias')
    );
}

    public function agregarCarrito($id)
    {
        $producto = Producto::findOrFail($id);

        $carrito = session()->get('carrito', []);

        if(isset($carrito[$id])){

            $carrito[$id]['cantidad']++;

        } else {

            $carrito[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen,
                'cantidad' => 1
            ];
        }

        session()->put('carrito', $carrito);

        return redirect('/productos')
            ->with('success', 'Producto agregado al carrito');
    }
}
