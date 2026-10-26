<?php

namespace App\Http\Controllers;

use App\Models\Reclamacion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReclamacionController extends Controller
{
    public function create()
    {
        return view('reclamaciones.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'documento' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^[+0-9 ()-]{7,20}$/'],
            'direccion' => ['required', 'string', 'max:255'],
            'menor' => ['nullable', 'boolean'],
            'representante' => ['nullable', 'required_if:menor,1', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['Reclamo', 'Queja'])],
            'bien_tipo' => ['required', Rule::in(['Producto', 'Servicio'])],
            'bien_descripcion' => ['required', 'string', 'max:3000'],
            'monto' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'detalle' => ['required', 'string', 'max:10000'],
            'solicitud' => ['required', 'string', 'max:5000'],
        ]);
        $reclamacion = Reclamacion::create([...$datos, 'menor' => $request->boolean('menor'), 'token' => Str::random(64),
            'proveedor' => ['nombre' => 'ByteStore', 'razon_social' => config('tienda.razon_social'),
                'ruc' => config('tienda.ruc'), 'direccion' => config('tienda.retiro.direccion')]]);

        return redirect()->route('reclamaciones.show', $reclamacion->token)->with('success', 'Tu hoja fue registrada. Conserva la constancia y su enlace privado.');
    }

    public function show(string $token)
    {
        $reclamacion = Reclamacion::where('token', $token)->firstOrFail();

        return response()->view('reclamaciones.show', compact('reclamacion'))->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
