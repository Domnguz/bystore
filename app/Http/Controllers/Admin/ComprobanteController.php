<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\Igv;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ComprobanteController extends Controller
{
    public function store(Request $request, int $id)
    {
        $pedido = Pedido::with('usuario', 'detalles')->findOrFail($id);
        if (! $pedido->entrega_cotizada || ! in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado'], true)) {
            throw ValidationException::withMessages(['comprobante_venta' => 'Confirma el pago y el total del pedido antes de generar el resumen.']);
        }

        $igv = $pedido->base_imponible !== null && $pedido->igv !== null
            ? [
                'base_imponible' => $pedido->base_imponible,
                'igv' => $pedido->igv,
                'total' => $pedido->total,
                'tasa' => number_format((float) ($pedido->igv_tasa ?? config('tienda.igv_porcentaje', 18)), 2, '.', ''),
            ]
            : Igv::desglosar($pedido->total, $pedido->igv_tasa ?? config('tienda.igv_porcentaje', 18));
        $contenido = Pdf::loadView('pedidos.boleta-pdf', compact('pedido', 'igv'))->setPaper('a4')->output();
        $ruta = 'comprobantes-venta/resumen-pedido-'.$pedido->id.'-'.Str::uuid().'.pdf';
        if (! Storage::disk('local')->put($ruta, $contenido)) {
            throw new \RuntimeException('No se pudo guardar el resumen de compra.');
        }
        $anterior = null;
        try {
            DB::transaction(function () use ($request, $id, $ruta, &$anterior) {
                $pedido = Pedido::lockForUpdate()->findOrFail($id);
                if (! $pedido->entrega_cotizada || ! in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado'], true)) {
                    throw ValidationException::withMessages(['comprobante_venta' => 'El pedido debe estar pagado y tener el total confirmado.']);
                }
                $anterior = $pedido->comprobante_venta;
                $pedido->update(['comprobante_venta' => $ruta, 'actualizado_por' => $request->user()->id]);
                $pedido->eventos()->create(['estado' => $pedido->estado, 'usuario_id' => $request->user()->id,
                    'nota' => 'Resumen informativo de compra generado y disponible para el comprador.']);
            }, 3);
        } catch (Throwable $error) {
            Storage::disk('local')->delete($ruta);
            throw $error;
        }
        if ($anterior) {
            Storage::disk('local')->delete($anterior);
        }

        return back()->with('success', 'Resumen de compra generado y disponible para el cliente.');
    }
}
