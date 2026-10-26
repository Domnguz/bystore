<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\CodigoVerificacion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CorreoVerificacion
{
    public const MINUTOS_VALIDEZ = 10;

    public const MAX_INTENTOS = 5;

    public const NO_DISPONIBLE = 'El envío de correos aún no está disponible. Tu cuenta está guardada. Contacta a la tienda para completar la verificación.';

    public static function enModoPruebas(): bool
    {
        $mailer = config('mail.default');
        $transport = config('mail.mailers.'.$mailer.'.transport');

        return in_array($transport, ['log', 'array'], true);
    }

    public function enviar(User $user): void
    {
        Cache::lock($this->clave($user).':lock', 120)->block(5, function () use ($user) {
            $user->refresh();
            if ($user->hasVerifiedEmail()) {
                return;
            }

            $anterior = Cache::get($this->clave($user));
            do {
                $codigo = sprintf('%06d', random_int(0, 999999));
                $hash = $this->hash($codigo);
            } while ($hash === ($anterior['hash'] ?? null));

            $user->notify(new CodigoVerificacion($codigo));
            $vence = now()->addMinutes(self::MINUTOS_VALIDEZ);
            Cache::put($this->clave($user), [
                'hash' => $hash,
                'email' => $user->getEmailForVerification(),
                'expires_at' => $vence->timestamp,
                'attempts' => 0,
            ], $vence);
        });
    }

    public function verificar(User $user, string $codigo): bool
    {
        return Cache::lock($this->clave($user).':lock', 120)->block(5, function () use ($user, $codigo) {
            // Read the current email under a row lock so a profile edit cannot verify the wrong address.
            $resultado = DB::transaction(function () use ($user, $codigo) {
                $actual = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
                $clave = $this->clave($actual);
                if ($actual->hasVerifiedEmail()) {
                    Cache::forget($clave);

                    return false;
                }

                $desafio = Cache::get($clave);
                if (! $desafio || $desafio['expires_at'] <= now()->timestamp
                    || $desafio['email'] !== $actual->getEmailForVerification()) {
                    Cache::forget($clave);

                    return 'El código ha vencido o no está disponible. Solicita uno nuevo.';
                }

                if (! hash_equals($desafio['hash'], $this->hash($codigo))) {
                    $desafio['attempts']++;
                    if ($desafio['attempts'] >= self::MAX_INTENTOS) {
                        Cache::forget($clave);

                        return 'Alcanzaste el límite de intentos. Solicita un nuevo código.';
                    }
                    Cache::put($clave, $desafio, $desafio['expires_at'] - now()->timestamp);

                    return 'El código es incorrecto. Revisa los 6 dígitos del correo.';
                }

                $actual->markEmailAsVerified();
                Cache::forget($clave);
                $user->setRawAttributes($actual->getAttributes(), true);

                return true;
            });
            // Commit attempt counters even when the cache uses the same database connection.
            if (is_string($resultado)) {
                throw ValidationException::withMessages(['codigo' => $resultado]);
            }

            return $resultado;
        });
    }

    private function clave(User $user): string
    {
        return 'email-verification:'.$user->id;
    }

    private function hash(string $codigo): string
    {
        return hash_hmac('sha256', $codigo, (string) config('app.key'));
    }
}
