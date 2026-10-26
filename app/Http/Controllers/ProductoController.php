<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    private function filtrar(Request $request, $query)
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'min' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'max' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'disponible' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'in:nombre,precio_asc,precio_desc,novedades'],
        ]);
        if (! empty($datos['q'])) {
            $this->buscarPorTermino($query, $datos['q']);
        }
        if (isset($datos['min'])) {
            $query->where('precio', '>=', $datos['min']);
        }
        if (isset($datos['max'])) {
            $query->where('precio', '<=', $datos['max']);
        }
        if ($request->boolean('disponible')) {
            $query->where('stock', '>', 0);
        }
        match ($datos['orden'] ?? '') {
            'nombre' => $query->orderBy('nombre')->orderBy('id'),
            'precio_asc' => $query->orderBy('precio')->orderBy('id'),
            'precio_desc' => $query->orderByDesc('precio')->orderBy('id'),
            'novedades' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderBy('id'),
        };

        return $query;
    }

    private function buscarPorTermino($query, string $termino)
    {
        $coincidencia = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($termino)).'%';

        return $query->where(function ($busqueda) use ($coincidencia) {
            foreach (['nombre', 'marca', 'modelo', 'sku', 'descripcion', 'especificaciones', 'compatibilidad'] as $campo) {
                $busqueda->orWhere($campo, 'like', $coincidencia);
            }
            $busqueda->orWhereHas('categoria', fn ($categoria) => $categoria->where('nombre', 'like', $coincidencia));
        });
    }

    public function sugerencias(Request $request)
    {
        $datos = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $termino = trim($datos['q']);
        $coincidencia = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $termino).'%';
        $productos = $this->buscarPorTermino(Producto::with('categoria:id,nombre'), $termino)
            ->orderByRaw('CASE WHEN nombre LIKE ? THEN 0 ELSE 1 END', [$coincidencia])
            ->orderBy('nombre')
            ->limit(6)
            ->get();

        return response()->json($productos->map(fn (Producto $producto) => [
            'nombre' => $producto->nombre,
            'categoria' => $producto->categoria?->nombre,
            'precio' => $producto->precio,
            'imagen' => $producto->imagen ? asset('storage/'.$producto->imagen) : null,
            'url' => route('tienda.productos.show', $producto->id),
        ]))->header('Cache-Control', 'no-store');
    }

    public function index(Request $request)
    {
        $productos = $this->filtrar($request, Producto::with('categoria'))->paginate(12)->withQueryString();
        $categorias = Categoria::all();
        $categoriaGrafica = $categorias->first(fn (Categoria $categoria) => str_contains(mb_strtolower($categoria->nombre), 'tarjeta'));
        $graficaDestacada = $categoriaGrafica
            ? Producto::where('categoria_id', $categoriaGrafica->id)->whereNotNull('imagen')->orderBy('id')->first()
            : null;
        $banners = Banner::where('activo', true)->orderBy('orden')->orderBy('id')->get();

        return view('productos', compact('productos', 'categorias', 'banners', 'categoriaGrafica', 'graficaDestacada'));
    }

    public function show(int $id)
    {
        $producto = Producto::with(['categoria', 'imagenes', 'resenas' => fn ($query) => $query->latest()])
            ->withAvg('resenas', 'calificacion')
            ->withCount('resenas')
            ->findOrFail($id);

        return view('productos.show', compact('producto'));
    }

    public function categoria(Request $request, int $id)
    {
        $categoria = Categoria::findOrFail($id);
        $productos = $this->filtrar($request, Producto::where('categoria_id', $categoria->id))->paginate(12)->withQueryString();
        $categorias = Categoria::all();

        return view('categoria', compact('categoria', 'productos', 'categorias'));
    }
}
