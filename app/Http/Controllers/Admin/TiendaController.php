<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PagoYape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TiendaController extends Controller
{
    public function index()
    {
        return view('admin.tienda');
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'pagos_demo' => ['required', 'boolean'],
            'yape_numero' => ['nullable', 'digits:9'], 'yape_titular' => ['required', 'string', 'max:150'],
            'yape_qr' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'plin_numero' => ['nullable', 'digits:9'], 'plin_titular' => ['nullable', 'string', 'max:150'],
            'transferencia_banco' => ['nullable', 'string', 'max:100'],
            'transferencia_titular' => ['nullable', 'string', 'max:150'],
            'transferencia_cuenta' => ['nullable', 'string', 'max:50'],
            'transferencia_cci' => ['nullable', 'digits:20'],
            'direccion' => ['nullable', 'string', 'max:255'], 'contacto' => ['nullable', 'string', 'max:150'],
            'whatsapp' => ['nullable', 'regex:/^9[0-9]{8}$/'],
            'razon_social' => ['nullable', 'string', 'max:255'], 'ruc' => ['nullable', 'digits:11'],
        ]);
        $datos['pagos_demo'] = $request->boolean('pagos_demo');
        if (empty($datos['yape_numero']) && ! $request->hasFile('yape_qr') && ! PagoYape::qrUrl()) {
            throw ValidationException::withMessages(['yape_numero' => 'Registra un número de Yape o carga el QR del negocio.']);
        }
        if ((bool) ($datos['plin_numero'] ?? null) !== (bool) ($datos['plin_titular'] ?? null)) {
            throw ValidationException::withMessages(['plin_numero' => 'Completa el número y el titular de Plin para habilitar ese método.']);
        }
        if (collect(['transferencia_banco', 'transferencia_titular', 'transferencia_cuenta', 'transferencia_cci'])->filter(fn ($campo) => !empty($datos[$campo]))->isNotEmpty()
            && (empty($datos['transferencia_banco']) || empty($datos['transferencia_titular']) || (empty($datos['transferencia_cuenta']) && empty($datos['transferencia_cci'])))) {
            throw ValidationException::withMessages(['transferencia_banco' => 'Completa banco, titular y número de cuenta o CCI para habilitar la transferencia.']);
        }
        $qrAnterior = config('tienda.yape.qr');
        $qrNuevo = $request->hasFile('yape_qr') ? $request->file('yape_qr')->store('pagos/yape', 'public') : null;
        if ($qrNuevo === false) {
            throw new \RuntimeException('No se pudo guardar el QR.');
        }
        $datos['yape_qr'] = $qrNuevo ?: ($qrAnterior ?? '');
        $ruta = Storage::disk('local')->path('tienda.json');
        if (! is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0700, true);
        }
        $temporal = tempnam(dirname($ruta), 'tienda-');
        try {
            if (file_put_contents($temporal, json_encode($datos, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), LOCK_EX) === false || ! rename($temporal, $ruta)) {
                throw new \RuntimeException('No se pudo guardar la configuración de la tienda.');
            }
        } catch (\Throwable $error) {
            if ($qrNuevo) Storage::disk('public')->delete($qrNuevo);
            throw $error;
        } finally {
            if (is_file($temporal)) {
                unlink($temporal);
            }
        }
        if ($qrNuevo && is_string($qrAnterior) && str_starts_with($qrAnterior, 'pagos/yape/')) {
            Storage::disk('public')->delete($qrAnterior);
        }

        return back()->with('success', 'Datos de tienda actualizados.');
    }
}
