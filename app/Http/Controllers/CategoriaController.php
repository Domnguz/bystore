<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::all();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        Categoria::create($request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ]));

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada correctamente');
    }

    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update($request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ]));

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada');
    }

    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->productos()->exists()) {
            return back()->withErrors(['categoria' => 'La categoría tiene productos. Reasígnalos antes de eliminarla.']);
        }
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada');
    }
}
