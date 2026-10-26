<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\Dinero;
use App\Services\Igv;
use App\Services\PedidoNotificaciones;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request, CarritoService $service)
    {
        $carrito = $service->sincronizar($request);
        if (! $carrito) {
            return redirect()->route('carrito');
        }
        $token = (string) Str::uuid();
        $request->session()->put('checkout_revision', ['token' => $token, 'huella' => $service->huella($carrito)]);
        $productosCentimos = collect($carrito)->sum(fn ($item) => Dinero::centimos((string) $item['precio']) * (int) $item['cantidad']);
        $igv = Igv::desglosar(Dinero::decimal($productosCentimos), config('tienda.igv_porcentaje', 18));

        return view('checkout', compact('carrito', 'token', 'igv'));
    }

    public function procesar(Request $request, CarritoService $service)
    {
        $datos = $request->validate([
            'checkout_token' => ['required', 'uuid'],
            'destinatario' => ['required', 'string', 'max:150'],
            'telefono' => ['required', 'string', 'regex:/^[+0-9 ()-]{7,20}$/'],
            'metodo_entrega' => ['required', Rule::in(array_keys(array_filter([
                'retiro' => config('tienda.retiro.habilitado'), 'envio' => config('tienda.envio.habilitado'),
            ])))],
            'departamento' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ]);
        $clave = hash('sha256', $request->user()->id.'|'.$datos['checkout_token']);
        if ($existente = Pedido::where('clave_checkout', $clave)->where('usuario_id', $request->user()->id)->first()) {
            return redirect()->route('mis-pedidos.show', $existente);
        }
        $revision = $request->session()->get('checkout_revision', []);
        if (! hash_equals((string) ($revision['token'] ?? ''), $datos['checkout_token'])) {
            throw ValidationException::withMessages(['carrito' => 'Vuelve al checkout para revisar el pedido actualizado.']);
        }
        $carrito = $request->session()->get('carrito', []);
        if (! $carrito) {
            return redirect()->route('carrito');
        }
        try {
            $pedido = DB::transaction(function () use ($request, $service, $datos, $clave, $revision, $carrito) {
                if ($existente = Pedido::where('clave_checkout', $clave)->lockForUpdate()->first()) {
                    return $existente;
                }
                $productos = Producto::whereIn('id', array_keys($carrito))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $actual = [];
                $centimos = 0;
                foreach ($carrito as $id => $item) {
                    $producto = $productos->get($id);
                    $cantidad = (int) $item['cantidad'];
                    if (! $producto || $cantidad < 1 || $cantidad > 99 || $producto->stock < $cantidad) {
                        throw ValidationException::withMessages(['carrito' => 'La disponibilidad ha cambiado. Revisa tu carrito antes de continuar.']);
                    }
                    $actual[$id] = ['id' => $id, 'cantidad' => $cantidad, 'precio' => $producto->precio];
                    $centimos += Dinero::centimos($producto->precio) * $cantidad;
                }
                if (! hash_equals($revision['huella'], $service->huella($actual))) {
                    throw ValidationException::withMessages(['carrito' => 'Los precios o cantidades cambiaron. Vuelve al checkout para revisar el nuevo total.']);
                }
                if ($centimos > 9999999999) {
                    throw ValidationException::withMessages(['carrito' => 'El importe supera el máximo permitido para un pedido.']);
                }
                $igv = Igv::desglosar(Dinero::decimal($centimos), config('tienda.igv_porcentaje', 18));
                $pedido = Pedido::create([
                    ...collect($datos)->except('checkout_token')->all(),
                    'usuario_id' => $request->user()->id, 'email_contacto' => $request->user()->email,
                    'fecha' => now(), 'clave_checkout' => $clave, 'estado' => 'Pendiente',
                    'total' => Dinero::decimal($centimos), 'subtotal_productos' => Dinero::decimal($centimos),
                    'base_imponible' => $igv['base_imponible'], 'igv' => $igv['igv'], 'igv_tasa' => $igv['tasa'],
                    'costo_envio' => $datos['metodo_entrega'] === 'retiro' ? '0.00' : null,
                    'entrega_cotizada' => $datos['metodo_entrega'] === 'retiro', 'stock_reservado' => true,
                    'reserva_hasta' => now()->addHours(config('tienda.reserva_horas', 48)),
                ]);
                foreach ($actual as $id => $item) {
                    $producto = $productos->get($id);
                    $pedido->detalles()->create(['producto_id' => $id, 'nombre_producto' => $producto->nombre,
                        'sku_producto' => $producto->sku,
                        'cantidad' => $item['cantidad'], 'precio' => $producto->precio,
                        'subtotal' => Dinero::decimal(Dinero::centimos($producto->precio) * $item['cantidad'])]);
                    // La condición protege también motores donde el bloqueo no sea de filas.
                    if (Producto::whereKey($id)->where('stock', '>=', $item['cantidad'])->decrement('stock', $item['cantidad']) !== 1) {
                        throw ValidationException::withMessages(['carrito' => 'Se agotó un producto. No se registró la compra.']);
                    }
                }
                $pedido->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => 'Pendiente',
                    'nota' => 'Pedido registrado. Pago pendiente de verificación.']);

                return $pedido;
            }, 3);
        } catch (QueryException $error) {
            // Un POST concurrente con la misma clave puede encontrar la restricción unique.
            $pedido = Pedido::where('clave_checkout', $clave)->where('usuario_id', $request->user()->id)->first();
            if (! $pedido) {
                throw $error;
            }
        }
        $request->session()->forget('carrito');
        PedidoNotificaciones::enviar($pedido);

        return redirect()->route('mis-pedidos.show', $pedido)->with('success', 'Pedido registrado. Revisa las instrucciones de entrega y pago.');
    }
}
