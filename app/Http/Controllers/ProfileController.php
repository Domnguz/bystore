<?php

namespace App\Http\Controllers;

use App\Services\CorreoVerificacion;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $vista = $request->user()->rol === 'admin' ? 'admin.profile' : 'profile';

        return view($vista, ['usuario' => $request->user()]);
    }

    public function update(Request $request)
    {
        $usuario = $request->user();
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'telefono' => ['nullable', 'string', 'regex:/^[+0-9 ()-]{7,20}$/'],
            'dni' => ['required', 'string', 'regex:/^[0-9]{8}$/D', Rule::unique('users', 'dni')->ignore($usuario->id)],
            'privacy_accepted' => $usuario->dni ? ['sometimes', 'accepted'] : ['accepted'],
        ], [
            'dni.required' => 'Completa tu DNI para mantener una sola cuenta por documento.',
            'dni.regex' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Ese DNI ya está asociado a otra cuenta. Recupera el acceso a tu cuenta original o contacta al negocio.',
            'privacy_accepted.accepted' => 'Acepta el uso del DNI descrito en el aviso de privacidad.',
        ]);
        unset($datos['privacy_accepted']);
        try {
            $cambioCorreo = DB::transaction(function () use ($usuario, $datos) {
                $actual = $usuario->newQuery()->lockForUpdate()->findOrFail($usuario->id);
                if ($actual->dni && $actual->dni !== $datos['dni']) {
                    throw ValidationException::withMessages(['dni' => 'El DNI de tu cuenta no se puede cambiar desde el perfil. Contacta al negocio para solicitar una corrección.']);
                }
                $cambioCorreo = $actual->email !== $datos['email'];
                if (! $actual->dni) {
                    $actual->dni_consent_at = now();
                }
                $actual->forceFill($datos);
                if ($cambioCorreo) {
                    $actual->email_verified_at = null;
                }
                $actual->save();

                return $cambioCorreo;
            });
        } catch (UniqueConstraintViolationException $error) {
            throw ValidationException::withMessages(['dni' => 'El DNI o correo ya está asociado a otra cuenta. Recupera tu acceso o contacta al negocio.']);
        }
        $usuario->refresh();
        if ($cambioCorreo) {
            try {
                $usuario->sendEmailVerificationNotification();
            } catch (TransportExceptionInterface $error) {
                report($error);

                return redirect()->route('verification.notice')->with('error', 'Perfil guardado. No pudimos enviar el correo; solicita un nuevo código desde esta pantalla.');
            }

            if (CorreoVerificacion::enModoPruebas()) {
                return redirect()->route('verification.notice')->with('error', 'Perfil guardado. '.CorreoVerificacion::NO_DISPONIBLE);
            }

            return redirect()->route('verification.notice')->with('success', 'Perfil guardado. Verifica tu nuevo correo antes de realizar pedidos.');
        }

        return redirect()->intended('/profile')->with('success', 'Perfil actualizado.');
    }

    public function password(Request $request)
    {
        $datos = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);
        $request->user()->forceFill(['password' => $datos['password']])->save();
        $request->session()->regenerate();

        return back()->with('success', 'Contraseña actualizada.');
    }
}
