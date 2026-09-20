<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BannerController extends Controller
{
    // Lista todos los banners, incluidos los desactivados.
    public function index()
    {
        $banners = Banner::orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('admin.banners.index', compact('banners'));
    }

    // Guarda un banner nuevo.
    public function store(Request $request)
    {
        $datos = $this->validar($request, true);
        $archivosNuevos = [];

        try {
            foreach (['imagen', 'imagen_movil'] as $campo) {
                if ($request->hasFile($campo)) {
                    $datos[$campo] = $this->guardarImagen(
                        $request->file($campo),
                        $archivosNuevos
                    );
                }
            }

            Banner::create($datos);
        } catch (Throwable $error) {
            // Si falla el guardado, elimina los archivos recién subidos.
            Storage::disk('public')->delete($archivosNuevos);
            throw $error;
        }

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner creado correctamente.');
    }

    // Cambia el título, orden, estado o imágenes de un banner.
public function update(Request $request, Banner $banner)
{
    $datos = $this->validar($request, false);

    $request->validate([
        'quitar_imagen_movil' => ['nullable', 'boolean'],
    ]);

    // Evita pedir eliminar y reemplazar la misma foto a la vez.
    if (
        $request->boolean('quitar_imagen_movil')
        && $request->hasFile('imagen_movil')
    ) {
        return back()->withErrors([
            'imagen_movil' =>
                'Elige reemplazar la foto móvil o eliminarla, pero no ambas opciones.',
        ]);
    }

    $archivosNuevos = [];
    $archivosAnteriores = [];

    try {
        foreach (['imagen', 'imagen_movil'] as $campo) {
            if ($request->hasFile($campo)) {
                $datos[$campo] = $this->guardarImagen(
                    $request->file($campo),
                    $archivosNuevos
                );

                if ($banner->{$campo}) {
                    $archivosAnteriores[] = $banner->{$campo};
                }
            }
        }

        // Quita la foto móvil cuando se marca la casilla.
        if ($request->boolean('quitar_imagen_movil')) {
            if ($banner->imagen_movil) {
                $archivosAnteriores[] = $banner->imagen_movil;
            }

            $datos['imagen_movil'] = null;
        }

        $banner->update($datos);
    } catch (Throwable $error) {
        Storage::disk('public')->delete($archivosNuevos);
        throw $error;
    }

    Storage::disk('public')->delete($archivosAnteriores);

    return redirect()
        ->route('admin.banners.index')
        ->with('success', 'Banner actualizado correctamente.');
}

    public function destroy(Banner $banner)
    {
        $archivos = array_filter([
            $banner->imagen,
            $banner->imagen_movil,
        ]);

        // Primero elimina el registro; después, sus archivos.
        $banner->delete();

        Storage::disk('public')->delete($archivos);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner eliminado correctamente.');
    }

    private function validar(Request $request, bool $nuevo): array
    {
        $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'orden' => ['required', 'integer', 'min:0', 'max:9999'],
            'activo' => ['required', 'boolean'],

            'imagen' => [
                $nuevo ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'imagen_movil' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        return [
            'titulo' => $request->input('titulo'),
            'orden' => (int) $request->input('orden'),
            'activo' => $request->boolean('activo'),
        ];
    }

    private function guardarImagen($imagen, array &$archivos): string
    {
        $ruta = $imagen->store('banners', 'public');

        if (!$ruta) {
            throw new \RuntimeException('No se pudo guardar la imagen.');
        }

        $archivos[] = $ruta;

        return $ruta;
    }
}