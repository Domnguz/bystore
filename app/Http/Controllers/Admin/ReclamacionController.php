<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reclamacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReclamacionController extends Controller
{
    public function index()
    {
        return view('admin.reclamaciones', ['reclamaciones' => Reclamacion::latest()->paginate(15)]);
    }

    public function responder(Request $request, int $id)
    {
        $datos = $request->validate([
            'respuesta' => ['required', 'string', 'max:10000'],
            'medio_respuesta' => ['required', Rule::in(['Correo electrónico', 'Carta'])],
            'evidencia_respuesta' => ['required', 'string', 'max:255'],
            'respuesta_entregada' => ['accepted'],
        ]);
        unset($datos['respuesta_entregada']);
        Reclamacion::findOrFail($id)->update([...$datos, 'respondido_at' => now(), 'respondido_por' => $request->user()->id]);

        return back()->with('success', 'Respuesta y constancia de entrega registradas.');
    }
}
