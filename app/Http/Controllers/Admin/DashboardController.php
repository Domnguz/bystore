<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\Dinero;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->validate([
            'periodo' => ['nullable', Rule::in(['dia', 'semana', 'mes'])],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $periodo = $datos['periodo'] ?? 'mes';
        $fechaSeleccionada = $datos['fecha'] ?? CarbonImmutable::now('America/Lima')->format('Y-m-d');
        $fecha = CarbonImmutable::createFromFormat('!Y-m-d', $fechaSeleccionada, 'America/Lima');
        $inicio = match ($periodo) {
            'dia' => $fecha,
            'semana' => $fecha->startOfWeek(CarbonInterface::MONDAY),
            default => $fecha->startOfMonth(),
        };
        $finExclusivo = match ($periodo) {
            'dia' => $inicio->addDay(),
            'semana' => $inicio->addWeek(),
            default => $inicio->addMonth(),
        };
        // The database stores UTC. Reports use the local calendar in Peru.
        $limites = [
            $inicio->utc()->format('Y-m-d H:i:s'),
            $finExclusivo->utc()->format('Y-m-d H:i:s'),
        ];
        $ventas = Pedido::query()
            ->whereIn('estado', ['Pagado', 'Enviado', 'Entregado'])
            ->whereRaw('COALESCE(pagado_at, fecha, created_at) >= ? AND COALESCE(pagado_at, fecha, created_at) < ?', $limites)
            ->get(['id', 'total', 'pagado_at', 'fecha', 'created_at']);
        $ventasCentimos = $ventas->sum(fn (Pedido $pedido) => Dinero::centimos($pedido->total));
        $ventasTotales = Dinero::decimal($ventasCentimos);
        $ventasConfirmadas = $ventas->count();
        $ticketPromedio = Dinero::decimal($ventasConfirmadas ? (int) round($ventasCentimos / $ventasConfirmadas) : 0);
        $ventasSinFechaPago = $ventas->whereNull('pagado_at')->count();

        $puntosVentas = [];
        for ($punto = $inicio; $punto->lt($finExclusivo); $punto = $periodo === 'dia' ? $punto->addHour() : $punto->addDay()) {
            $clave = $punto->format($periodo === 'dia' ? 'H' : 'Y-m-d');
            $puntosVentas[$clave] = [
                'etiqueta' => $periodo === 'dia' ? $punto->format('H:i') : $punto->locale('es')->translatedFormat('d M'),
                'total_centimos' => 0,
                'pedidos' => 0,
            ];
        }
        foreach ($ventas as $venta) {
            $fechaVenta = CarbonImmutable::instance($venta->pagado_at ?? $venta->fecha ?? $venta->created_at)->setTimezone('America/Lima');
            $clave = $fechaVenta->format($periodo === 'dia' ? 'H' : 'Y-m-d');
            $puntosVentas[$clave]['total_centimos'] += Dinero::centimos($venta->total);
            $puntosVentas[$clave]['pedidos']++;
        }
        $puntosVentas = array_values($puntosVentas);

        $pedidosPeriodo = Pedido::query()
            ->whereRaw('COALESCE(fecha, created_at) >= ? AND COALESCE(fecha, created_at) < ?', $limites);
        $resumenEstados = (clone $pedidosPeriodo)->selectRaw('estado, COUNT(*) AS cantidad')->groupBy('estado')->pluck('cantidad', 'estado');
        $pedidosRecibidos = $resumenEstados->sum();
        $ultimosPedidos = (clone $pedidosPeriodo)->with('usuario')->orderByDesc('fecha')->orderByDesc('id')->paginate(10)->withQueryString();
        $rangoFechas = $inicio->locale('es')->translatedFormat('d M Y');
        if ($periodo !== 'dia') {
            $rangoFechas .= ' — '.$finExclusivo->subDay()->locale('es')->translatedFormat('d M Y');
        }

        return view('admin.dashboard', [
            'periodo' => $periodo,
            'fechaSeleccionada' => $fechaSeleccionada,
            'rangoFechas' => $rangoFechas,
            'ventasTotales' => $ventasTotales,
            'ventasConfirmadas' => $ventasConfirmadas,
            'ticketPromedio' => $ticketPromedio,
            'ventasSinFechaPago' => $ventasSinFechaPago,
            'puntosVentas' => $puntosVentas,
            'resumenEstados' => $resumenEstados,
            'pedidosRecibidos' => $pedidosRecibidos,
            'ultimosPedidos' => $ultimosPedidos,
            'totalProductos' => Producto::count(),
            'totalCategorias' => Categoria::count(),
            'totalPedidos' => Pedido::count(),
            'pedidosPendientes' => Pedido::where('estado', 'Pendiente')->count(),
            'pagosPendientes' => Pedido::where('estado', 'Pendiente')->whereNotNull('comprobante_pago')->count(),
            'productosStockBajo' => Producto::where('stock', '<=', config('tienda.stock_bajo_limite', 5))->count(),
            'limiteStockBajo' => config('tienda.stock_bajo_limite', 5),
        ]);
    }
}
