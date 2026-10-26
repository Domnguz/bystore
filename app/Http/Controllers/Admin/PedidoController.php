<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\Dinero;
use App\Services\Igv;
use App\Services\PedidoNotificaciones;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    private function listado(Request $request)
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Rechazado'])],
        ]);
        $consulta = Pedido::query();
        if (filled($filtros['buscar'] ?? null)) {
            $termino = trim($filtros['buscar']);
            $consulta->where(function ($query) use ($termino) {
                $query->where('id', 'like', '%'.$termino.'%')
                    ->orWhereHas('usuario', function ($cliente) use ($termino) {
                        $cliente->where('name', 'like', '%'.$termino.'%')
                            ->orWhere('email', 'like', '%'.$termino.'%');
                    });
            });
        }
        if (filled($filtros['estado'] ?? null)) {
            $consulta->where('estado', $filtros['estado']);
        }

        $resumen = (clone $consulta)->selectRaw('estado, COUNT(*) as cantidad')->groupBy('estado')->pluck('cantidad', 'estado');
        $pagosPendientes = Pedido::where('estado', 'Pendiente')->whereNotNull('comprobante_pago')->count();
        $pedidos = (clone $consulta)->with('usuario')
            ->orderByRaw("CASE WHEN estado = 'Pendiente' AND comprobante_pago IS NOT NULL THEN 0 ELSE 1 END")
            ->latest('comprobante_enviado_at')->latest('id')->paginate(20)->withPath(route('pedidos.index'))->withQueryString();
        $cantidadResultados = $pedidos->total();
        $totalPedidos = Pedido::count();
        $lista = view('admin.pedidos._lista', compact('pedidos'))->render();
        $revision = hash('sha256', $lista.json_encode($resumen).$pagosPendientes.$cantidadResultados.$totalPedidos);

        return compact('resumen', 'pagosPendientes', 'cantidadResultados', 'totalPedidos', 'filtros', 'lista', 'revision');
    }

    public function index(Request $request)
    {
        return view('admin.pedidos.index', $this->listado($request));
    }

    public function actualizaciones(Request $request)
    {
        $request->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000'], 'resumen' => ['nullable', 'boolean']]);
        $datos = $request->boolean('resumen')
            ? ['pagosPendientes' => Pedido::where('estado', 'Pendiente')->whereNotNull('comprobante_pago')->count()]
            : $this->listado($request);

        return response()->json($datos)->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, int $id)
    {
        $pedido = Pedido::with('usuario', 'detalles.producto', 'eventos')->findOrFail($id);
        if ($request->wantsJson()) {
            return response()->json(['estado' => $pedido->estado_visible, 'revision' => $pedido->revision_pago])
                ->header('Cache-Control', 'private, no-store');
        }

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function cambiarEstado(Request $request, int $id, PedidoService $service)
    {
        $datos = $request->validate(['estado' => ['required', Rule::in(['Enviado', 'Entregado'])],
            'transportista' => ['nullable', 'string', 'max:100'], 'seguimiento' => ['nullable', 'string', 'max:150']]);
        $actual = Pedido::findOrFail($id);
        $nota = $actual->metodo_entrega === 'retiro'
            ? ($datos['estado'] === 'Enviado' ? 'Pedido listo para retirar en tienda.' : 'Pedido recogido en tienda.')
            : '';
        if ($actual->metodo_entrega === 'retiro') {
            $datos['transportista'] = null;
            $datos['seguimiento'] = null;
        }
        $pedido = $service->cambiarEstado($id, $datos['estado'], $request->user()->id, $datos, $nota);
        PedidoNotificaciones::enviar($pedido);

        $mensaje = $actual->metodo_entrega === 'retiro'
            ? ($datos['estado'] === 'Enviado' ? 'El pedido quedó listo para retirar en tienda.' : 'El pedido quedó marcado como recogido.')
            : 'Estado actualizado.';

        return back()->with('success', $mensaje);
    }

    public function aprobar(Request $request, int $id, PedidoService $service)
    {
        $datos = $request->validate(['referencia_pago' => ['required', 'string', 'max:150'], 'pago_verificado' => ['accepted']]);
        $pedido = $service->cambiarEstado($id, 'Pagado', $request->user()->id, $datos, 'Pago verificado manualmente por administración.');
        PedidoNotificaciones::enviar($pedido);

        return back()->with('success', 'Pago registrado. El stock ya estaba reservado y no se descontó nuevamente.');
    }

    public function rechazar(Request $request, int $id, PedidoService $service)
    {
        $pedido = $service->cambiarEstado($id, 'Rechazado', $request->user()->id, [], 'Pedido cancelado por administración.');
        PedidoNotificaciones::enviar($pedido);

        return back()->with('success', 'Pedido cancelado. La reserva se liberó una sola vez.');
    }

    public function cotizar(Request $request, int $id)
    {
        $datos = $request->validate(['costo_envio' => ['required', 'decimal:0,2', 'numeric', 'min:0', 'max:999999.99'],
            'metodo_entrega' => ['nullable', Rule::in(['envio', 'retiro'])],
            'direccion' => ['sometimes', 'nullable', 'string', 'max:255'],
            'departamento' => ['sometimes', 'nullable', 'string', 'max:100'],
            'provincia' => ['sometimes', 'nullable', 'string', 'max:100'],
            'distrito' => ['sometimes', 'nullable', 'string', 'max:100'],
            'referencia' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
        $pedido = DB::transaction(function () use ($request, $id, $datos) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);
            if ($pedido->estado !== 'Pendiente' || $pedido->comprobante_pago) {
                throw ValidationException::withMessages(['costo_envio' => 'Solo puedes cotizar envíos pendientes sin comprobante de pago.']);
            }
            $metodo = $pedido->metodo_entrega ?? ($datos['metodo_entrega'] ?? null);
            if (! $metodo || ($metodo === 'retiro' && Dinero::centimos((string) $datos['costo_envio']) !== 0)) {
                throw ValidationException::withMessages(['metodo_entrega' => 'Indica la entrega; el retiro debe tener costo de envío cero.']);
            }
            $subtotal = $pedido->subtotal_productos ?? $pedido->total;
            $total = Dinero::centimos($subtotal) + Dinero::centimos((string) $datos['costo_envio']);
            if ($total > 9999999999) {
                throw ValidationException::withMessages(['costo_envio' => 'Total fuera del límite permitido.']);
            }
            $igv = Igv::desglosar(Dinero::decimal($total), $pedido->igv_tasa ?: config('tienda.igv_porcentaje', 18));
            $pedido->update([...collect($datos)->only(['direccion', 'departamento', 'provincia', 'distrito', 'referencia'])->all(),
                'costo_envio' => Dinero::decimal(Dinero::centimos((string) $datos['costo_envio'])),
                'subtotal_productos' => $subtotal, 'total' => Dinero::decimal($total), 'entrega_cotizada' => true,
                'base_imponible' => $igv['base_imponible'], 'igv' => $igv['igv'], 'igv_tasa' => $igv['tasa'],
                'metodo_entrega' => $metodo,
                'actualizado_por' => $request->user()->id]);
            $pedido->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => $pedido->estado,
                'nota' => 'Datos de entrega confirmados por la tienda. Envío cotizado: S/ '.$pedido->costo_envio]);

            return $pedido;
        }, 3);
        PedidoNotificaciones::enviar($pedido);

        return back()->with('success', 'Costo de envío actualizado. El cliente ya puede revisar el total.');
    }
}
