<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.usuarios.index', ['usuarios' => User::orderBy('id')->paginate(20)]);
    }

    public function create()
    {
        return view('admin.usuarios.create');
    }

    public function edit(int $id)
    {
        return view('admin.usuarios.edit', ['usuario' => User::findOrFail($id)]);
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'], 'apellidos' => ['nullable', 'string', 'max:150'],
            'dni' => ['nullable', 'digits:8', Rule::unique('users', 'dni')->ignore($usuario?->id)],
            'telefono' => ['nullable', 'string', 'regex:/^[+0-9 ()-]{7,20}$/'],
            'genero' => ['nullable', Rule::in(['Masculino', 'Femenino'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'string', Password::min(10)->letters()->numbers()],
            'rol' => ['required', Rule::in(['admin', 'cliente'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        unset($datos['foto']);
        $ruta = $request->hasFile('foto') ? $request->file('foto')->store('usuarios', 'public') : null;
        try {
            User::create([...$datos, 'foto' => $ruta]);
        } catch (Throwable $error) {
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            } throw $error;
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $usuario = User::findOrFail($id);
        $datos = $this->validar($request, $usuario);
        unset($datos['foto']);
        if (empty($datos['password'])) {
            unset($datos['password']);
        }
        $ruta = $request->hasFile('foto') ? $request->file('foto')->store('usuarios', 'public') : null;
        $anterior = $usuario->foto;
        try {
            DB::transaction(function () use ($id, $datos, $ruta) {
                $admins = User::where('rol', 'admin')->orderBy('id')->lockForUpdate()->get();
                $usuario = User::lockForUpdate()->findOrFail($id);
                if ($usuario->rol === 'admin' && $datos['rol'] !== 'admin' && $admins->count() <= 1) {
                    throw ValidationException::withMessages(['rol' => 'No puedes quitar el acceso al último administrador.']);
                }
                $usuario->fill($datos);
                if ($usuario->isDirty('email')) {
                    $usuario->email_verified_at = null;
                }
                if ($ruta) {
                    $usuario->foto = $ruta;
                }
                $usuario->save();
            }, 3);
        } catch (Throwable $error) {
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            } throw $error;
        }
        if ($ruta && $anterior) {
            Storage::disk('public')->delete($anterior);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, int $id)
    {
        $foto = DB::transaction(function () use ($request, $id) {
            $admins = User::where('rol', 'admin')->orderBy('id')->lockForUpdate()->get();
            $usuario = User::lockForUpdate()->findOrFail($id);
            if ($usuario->id === $request->user()->id || ($usuario->rol === 'admin' && $admins->count() <= 1)) {
                throw ValidationException::withMessages(['usuario' => 'No puedes eliminar tu cuenta ni al último administrador.']);
            }
            if (Pedido::where('usuario_id', $id)->exists()) {
                throw ValidationException::withMessages(['usuario' => 'El usuario tiene pedidos y se debe conservar su historial.']);
            }
            $foto = $usuario->foto;
            $usuario->delete();

            return $foto;
        }, 3);
        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return back()->with('success', 'Usuario eliminado.');
    }
}
