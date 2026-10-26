<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('verification-code', fn (Request $request) => [
            Limit::perMinute(6)->by('verification-code:user:'.$request->user()->id),
            Limit::perHour(30)->by('verification-code:user:'.$request->user()->id),
            Limit::perMinute(30)->by('verification-code:ip:'.$request->ip()),
        ]);
        RateLimiter::for('registration', fn (Request $request) => [
            Limit::perMinute(6)->by('registration:'.$request->ip()),
            Limit::perHour(20)->by('registration:'.$request->ip()),
        ]);
        RateLimiter::for('verification', fn (Request $request) => [
            Limit::perMinute(1)->by('verification:user:'.$request->user()->id),
            Limit::perHour(6)->by('verification:user:'.$request->user()->id),
            Limit::perHour(20)->by('verification:ip:'.$request->ip()),
        ]);
        RateLimiter::for('account-profile', fn (Request $request) => [
            Limit::perMinute(6)->by('profile:'.$request->user()->id),
            Limit::perHour(20)->by('profile:'.$request->user()->id),
        ]);
        $ruta = storage_path('app/private/tienda.json');
        if (is_file($ruta)) {
            try {
                $datos = json_decode(file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);
                foreach (['yape_numero' => 'yape.numero', 'yape_titular' => 'yape.titular', 'yape_qr' => 'yape.qr',
                    'pagos_demo' => 'pagos_demo',
                    'plin_numero' => 'plin.numero', 'plin_titular' => 'plin.titular',
                    'transferencia_banco' => 'transferencia.banco', 'transferencia_titular' => 'transferencia.titular',
                    'transferencia_cuenta' => 'transferencia.cuenta', 'transferencia_cci' => 'transferencia.cci',
                    'direccion' => 'retiro.direccion', 'contacto' => 'contacto', 'whatsapp' => 'whatsapp',
                    'razon_social' => 'razon_social', 'ruc' => 'ruc'] as $campo => $clave) {
                    if (array_key_exists($campo, $datos) && (is_string($datos[$campo]) || is_bool($datos[$campo]) || $datos[$campo] === null)) {
                        config(['tienda.'.$clave => $datos[$campo] ?? '']);
                    }
                }
            } catch (\Throwable $error) {
                report($error);
            }
        }
        RateLimiter::for('login', function (Request $request) {
            $mensaje = 'Por seguridad, has realizado varios intentos. Intenta más tarde.';
            $respuesta = fn (Request $request, array $headers) => redirect()->route('login')->withErrors(['email' => $mensaje]);

            return [
                Limit::perMinute(20)->by($request->ip())->response($respuesta),
                Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip())->response($respuesta),
            ];
        });
    }
}
