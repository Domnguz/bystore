<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')->get();

        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();

        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $archivosNuevos = [];

        try {
            DB::transaction(function () use (
                $request,
                $datos,
                &$archivosNuevos
            ) {
                $datos['imagen'] = null;

                if ($request->hasFile('imagen')) {
                    $datos['imagen'] = $this->guardarFoto(
                        $request->file('imagen'),
                        $archivosNuevos
                    );
                }

                $producto = Producto::create($datos);

                foreach ($request->file('imagenes', []) as $foto) {
                    $ruta = $this->guardarFoto(
                        $foto,
                        $archivosNuevos
                    );

                    $producto->imagenes()->create([
                        'ruta' => $ruta,
                    ]);
                }
            });
        } catch (Throwable $error) {
            Storage::disk('public')->delete($archivosNuevos);

            throw $error;
        }

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(string $id)
    {
        return $this->edit($id);
    }

    public function edit(string $id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);
        $categorias = Categoria::all();

        return view(
            'admin.productos.edit',
            compact('producto', 'categorias')
        );
    }

    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);
        $datos = $this->validar($request, $producto);

        $archivosNuevos = [];
        $archivosAntiguos = [];

        try {
            DB::transaction(function () use (
                $request,
                $producto,
                $datos,
                &$archivosNuevos,
                &$archivosAntiguos
            ) {
                if ($request->hasFile('imagen')) {
                    $datos['imagen'] = $this->guardarFoto(
                        $request->file('imagen'),
                        $archivosNuevos
                    );

                    if ($producto->imagen) {
                        $archivosAntiguos[] = $producto->imagen;
                    }
                }

                $producto->update($datos);

                // Solo permite quitar fotos de este producto.
                $fotosEliminar = $producto->imagenes()
                    ->whereIn(
                        'id',
                        $request->input('eliminar_imagenes', [])
                    )
                    ->get();

                foreach ($fotosEliminar as $foto) {
                    $archivosAntiguos[] = $foto->ruta;
                    $foto->delete();
                }

                foreach ($request->file('imagenes', []) as $foto) {
                    $ruta = $this->guardarFoto(
                        $foto,
                        $archivosNuevos
                    );

                    $producto->imagenes()->create([
                        'ruta' => $ruta,
                    ]);
                }
            });
        } catch (Throwable $error) {
            Storage::disk('public')->delete($archivosNuevos);

            throw $error;
        }

        Storage::disk('public')->delete($archivosAntiguos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);

        $rutas = $producto->imagenes
            ->pluck('ruta')
            ->all();

        if ($producto->imagen) {
            $rutas[] = $producto->imagen;
        }

        try {
            DB::transaction(function () use ($producto) {
                $producto->imagenes()->delete();
                $producto->delete();
            });
        } catch (QueryException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) === 1451) {
                return redirect()
                    ->route('productos.index')
                    ->with(
                        'error',
                        'No se puede eliminar este producto porque está asociado a un pedido.'
                    );
            }

            throw $error;
        }

        Storage::disk('public')->delete($rutas);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    private function validar(
        Request $request,
        ?Producto $producto = null
    ): array {
        $request->validate([
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists(Categoria::class, 'id'),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],

            'imagen' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            // Hasta 8 fotos adicionales por envío.
            'imagenes' => ['nullable', 'array', 'max:8'],
            'imagenes.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'eliminar_imagenes' => ['nullable', 'array'],
            'eliminar_imagenes.*' => [
                'integer',
                'distinct',
                Rule::exists('producto_imagenes', 'id')
                    ->where(
                        'producto_id',
                        $producto?->id ?? 0
                    ),
            ],
        ]);

        return $request->only([
            'categoria_id',
            'nombre',
            'descripcion',
            'precio',
            'stock',
        ]);
    }

    private function guardarFoto(
        $foto,
        array &$archivosNuevos
    ): string {
        $ruta = $foto->store('productos', 'public');

        if (!$ruta) {
            throw new \RuntimeException(
                'No se pudo guardar la imagen.'
            );
        }

        $archivosNuevos[] = $ruta;

        return $ruta;
    }
}
