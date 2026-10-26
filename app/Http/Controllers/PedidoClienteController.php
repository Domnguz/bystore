<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;
use App\Services\Dinero;
use App\Services\Igv;
use App\Services\PedidoNotificaciones;
use App\Services\PedidoService;
use App\Services\MetodosPago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PedidoClienteController extends Controller
{
    public function index(Request $request)
    {
        $pedidos = Pedido::where('usuario_id', $request->user()->id)->latest()->paginate(12);

        return view('pedidos.index', compact('pedidos'));
    }

    private function propio(Request $request, int $id): Pedido
    {
        return Pedido::where('usuario_id', $request->user()->id)->findOrFail($id);
    }

    public function show(Request $request, int $pedido)
    {
        $usuarioId = $request->user()->id;
        $pedido = $this->propio($request, $pedido)->load([
            'detalles.producto.resenas' => fn ($query) => $query->where('usuario_id', $usuarioId),
            'eventos',
        ]);
        $igv = $this->desgloseIgv($pedido);

        return view('pedidos.show', compact('pedido', 'igv'));
    }

    public function boletaPdf(Request $request, int $pedido)
    {
        $pedido = $this->propio($request, $pedido)->load('detalles', 'usuario');
        abort_unless(
            $pedido->entrega_cotizada && in_array($pedido->estado, ['Pagado', 'Enviado', 'Entregado'], true),
            403,
            'El PDF estará disponible cuando el pago del pedido esté confirmado.'
        );

        $igv = $this->desgloseIgv($pedido);
        $pdf = Pdf::loadView('pedidos.boleta-pdf', compact('pedido', 'igv'))->setPaper('a4');
        $documento = $pedido->tipo_comprobante === 'factura' ? 'resumen-compra' : 'boleta-informativa';

        return response()->streamDownload(
            fn () => print $pdf->output(),
            $documento.'-pedido-'.$pedido->id.'.pdf',
            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    private function desgloseIgv(Pedido $pedido): array
    {
        if ($pedido->base_imponible !== null && $pedido->igv !== null) {
            return [
                'base_imponible' => $pedido->base_imponible,
                'igv' => $pedido->igv,
                'total' => $pedido->total,
                'tasa' => number_format((float) ($pedido->igv_tasa ?? config('tienda.igv_porcentaje', 18)), 2, '.', ''),
            ];
        }

        return Igv::desglosar($pedido->total, $pedido->igv_tasa ?? config('tienda.igv_porcentaje', 18));
    }

    public function estado(Request $request, int $pedido)
    {
        $pedido = $this->propio($request, $pedido);

        return response()->json(['estado' => $pedido->estado_visible, 'revision' => $pedido->revision_pago])
            ->header('Cache-Control', 'private, no-store');
    }

    public function cancelar(Request $request, int $pedido, PedidoService $service)
    {
        $propio = $this->propio($request, $pedido);
        if (! in_array($propio->estado, ['Pendiente', 'Rechazado'], true)) {
            throw ValidationException::withMessages(['pedido' => 'Este pedido ya está en proceso. Contacta a la tienda para solicitar una devolución.']);
        }
        if ($propio->comprobante_pago) {
            throw ValidationException::withMessages(['pedido' => 'Ya enviaste un comprobante. Contacta a la tienda para revisar el pago antes de cancelar.']);
        }
        $actualizado = $service->cambiarEstado($pedido, 'Rechazado', $request->user()->id, [], 'Pedido cancelado por el comprador.');
        PedidoNotificaciones::enviar($actualizado);

        return back()->with('success', 'Pedido cancelado. Las unidades reservadas se liberaron.');
    }

    public function enviarComprobante(Request $request, int $pedido)
    {
        $this->propio($request, $pedido);
        $metodosPago = MetodosPago::disponibles();
        abort_if(empty($metodosPago), 422, 'La tienda todavía no tiene métodos de pago configurados.');
        $datos = $request->validate([
            'metodo_pago' => ['required', Rule::in(array_keys($metodosPago))],
            'comprobante' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'total_confirmado' => ['required', 'regex:/^[0-9]+\.[0-9]{2}$/'],
            'pagador_nombre' => ['required', 'string', 'max:150'],
            'pago_realizado_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'tipo_comprobante' => ['required', Rule::in(['boleta', 'factura'])],
            'documento' => ['nullable', 'required_if:tipo_comprobante,factura', 'regex:/^(?:[0-9]{8}|[0-9]{11})$/'],
            'razon_social' => ['nullable', 'required_if:tipo_comprobante,factura', 'string', 'max:255'],
        ]);
        if ($datos['tipo_comprobante'] === 'factura' && strlen($datos['documento'] ?? '') !== 11) {
            throw ValidationException::withMessages(['documento' => 'Para factura ingresa un RUC de 11 dígitos.']);
        }
        $fechaPago = Carbon::createFromFormat('Y-m-d\TH:i', $datos['pago_realizado_at'], 'America/Lima')->startOfMinute()->utc();
        if ($fechaPago->isFuture()) {
            throw ValidationException::withMessages(['pago_realizado_at' => 'La fecha del pago no puede estar en el futuro.']);
        }
        $ruta = $request->file('comprobante')->store('comprobantes-pago', 'local');
        if (! $ruta) {
            throw new \RuntimeException('No se pudo guardar la constancia.');
        }
        $anterior = null;
        try {
            DB::transaction(function () use ($request, $pedido, $datos, $ruta, $fechaPago, $metodosPago, &$anterior) {
                $orden = Pedido::where('usuario_id', $request->user()->id)->lockForUpdate()->findOrFail($pedido);
                if ($orden->estado !== 'Pendiente' || ! $orden->entrega_cotizada || ! $orden->stock_reservado
                    || ($orden->reserva_hasta && $orden->reserva_hasta->isPast())) {
                    throw ValidationException::withMessages(['comprobante' => 'El pedido no admite un pago en este momento. Revisa su estado o contacta a la tienda.']);
                }
                if ($orden->comprobante_pago) {
                    throw ValidationException::withMessages(['comprobante' => 'Ya recibimos tu constancia. El administrador está revisando este pago.']);
                }
                if (Dinero::centimos($datos['total_confirmado']) !== Dinero::centimos($orden->total)) {
                    throw ValidationException::withMessages(['comprobante' => 'El total cambió. Revisa el pedido actualizado antes de enviar la constancia.']);
                }
                $anterior = $orden->comprobante_pago;
                $orden->update(['comprobante_pago' => $ruta, 'comprobante_enviado_at' => now(),
                    'pagador_nombre' => trim($datos['pagador_nombre']), 'pago_realizado_at' => $fechaPago,
                    'metodo_pago' => $datos['metodo_pago'],
                    'tipo_comprobante' => $datos['tipo_comprobante'], 'documento' => $datos['documento'] ?? null,
                    'razon_social' => $datos['tipo_comprobante'] === 'factura' ? trim($datos['razon_social']) : null]);
                $orden->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => 'Pendiente',
                    'nota' => 'Constancia de '.$metodosPago[$datos['metodo_pago']].' recibida. Pendiente de verificación por la tienda.']);
            }, 3);
        } catch (Throwable $error) {
            Storage::disk('local')->delete($ruta);
            throw $error;
        }
        if ($anterior) {
            Storage::disk('local')->delete($anterior);
        }

        return back()->with('success', 'Constancia recibida. La tienda verificará el pago; tu pedido sigue pendiente.');
    }

    public function comprobante(Request $request, int $pedido)
    {
        $query = Pedido::query();
        if ($request->user()->rol !== 'admin') {
            $query->where('usuario_id', $request->user()->id);
        }
        $pedido = $query->findOrFail($pedido);
        abort_unless($pedido->comprobante_pago && Storage::disk('local')->exists($pedido->comprobante_pago), 404);

        return Storage::disk('local')->response($pedido->comprobante_pago, null, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function comprobanteVenta(Request $request, int $pedido)
    {
        $query = Pedido::query();
        if ($request->user()->rol !== 'admin') {
            $query->where('usuario_id', $request->user()->id);
        }
        $pedido = $query->findOrFail($pedido);
        abort_unless($pedido->comprobante_venta && Storage::disk('local')->exists($pedido->comprobante_venta), 404);

        return Storage::disk('local')->download($pedido->comprobante_venta, 'comprobante-pedido-'.$pedido->id.'.'.pathinfo($pedido->comprobante_venta, PATHINFO_EXTENSION), [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
