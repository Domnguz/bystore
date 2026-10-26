<?php

namespace App\Http\Controllers;

use App\Services\CorreoVerificacion;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->returnToAccount($request);
        }

        return response()->view('auth.verify-email', [
            'correoDisponible' => ! CorreoVerificacion::enModoPruebas(),
            'minutosValidez' => CorreoVerificacion::MINUTOS_VALIDEZ,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function verify(Request $request, CorreoVerificacion $correo)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->returnToAccount($request);
        }
        if (is_string($request->input('codigo'))) {
            $request->merge(['codigo' => trim($request->input('codigo'))]);
        }
        $datos = $request->validate(['codigo' => ['required', 'string', 'regex:/^[0-9]{6}$/D']], [
            'codigo.required' => 'Escribe el código que recibiste por correo.',
            'codigo.string' => 'El código debe tener exactamente 6 dígitos.',
            'codigo.regex' => 'El código debe tener exactamente 6 dígitos.',
        ]);
        if ($correo->verificar($request->user(), $datos['codigo'])) {
            event(new Verified($request->user()));
        }

        return $this->returnToAccount($request)->with('success', 'Tu correo ha sido verificado.');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->returnToAccount($request);
        }
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface $error) {
            report($error);

            return back()->with('error', 'No pudimos enviar el correo. Intenta reenviarlo en unos minutos.');
        }

        if (CorreoVerificacion::enModoPruebas()) {
            return back()->with('error', CorreoVerificacion::NO_DISPONIBLE);
        }

        return back()->with('success', 'Te enviamos un nuevo código de verificación. Revisa también la carpeta de spam.');
    }

    private function returnToAccount(Request $request)
    {
        $path = parse_url((string) $request->session()->get('url.intended', ''), PHP_URL_PATH) ?: '';
        $verificationPath = parse_url(route('verification.notice'), PHP_URL_PATH);
        if (str_starts_with($path, $verificationPath)) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended('/profile');
    }
}
