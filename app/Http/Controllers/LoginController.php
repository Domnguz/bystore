<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CorreoVerificacion;
use App\Services\RegistrationCaptcha;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_LOCKOUT_SECONDS = 900;

    public function login(Request $request)
    {
        $this->normalizeEmail($request);
        $datos = $request->validate(['email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'], 'remember' => ['nullable', 'boolean']]);
        $attemptKey = $this->loginAttemptKey($datos['email']);
        $lockoutKey = $attemptKey.':lockout';

        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            return back()->withErrors(['email' => 'Por seguridad, alcanzaste el límite de intentos. Intenta más tarde.'])
                ->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $datos['email'], 'password' => $datos['password']], $request->boolean('remember'))) {
            RateLimiter::hit($attemptKey, self::LOGIN_LOCKOUT_SECONDS);

            if (RateLimiter::attempts($attemptKey) >= self::MAX_LOGIN_ATTEMPTS) {
                RateLimiter::hit($lockoutKey, self::LOGIN_LOCKOUT_SECONDS);

                return back()->withErrors(['email' => 'Por seguridad, alcanzaste el límite de intentos. Intenta más tarde.'])
                    ->onlyInput('email');
            }

            return back()->withErrors(['email' => 'Correo o contraseña incorrectos.'])->onlyInput('email');
        }
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockoutKey);
        $request->session()->regenerate();

        if (! $request->user()->hasVerifiedEmail() && $request->user()->rol === 'cliente') {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended($request->user()->rol === 'admin' ? '/admin' : '/');
    }

    public function registrationForm(Request $request, RegistrationCaptcha $captcha)
    {
        $challenge = $captcha->issue($request);

        return ($request->expectsJson() ? response()->json($challenge) : response()->view('auth.register', ['captcha' => $challenge]))
            ->header('Cache-Control', 'no-store, private');
    }

    public function register(Request $request, RegistrationCaptcha $captcha)
    {
        $this->normalizeEmail($request);
        if (! $captcha->consume($request) || $request->filled('website')) {
            throw ValidationException::withMessages(['captcha' => 'El código de seguridad es incorrecto o ha vencido. Carga un nuevo código e inténtalo otra vez.']);
        }
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'string', 'regex:/^[0-9]{8}$/D', 'unique:users,dni'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
            'privacy_accepted' => ['accepted'],
        ], [
            'name.required' => 'Ingresa tus nombres.',
            'apellidos.required' => 'Ingresa tus apellidos.',
            'apellidos.string' => 'Los apellidos deben ser texto.',
            'apellidos.max' => 'Los apellidos no deben superar los 150 caracteres.',
            'dni.required' => 'Ingresa tu DNI.',
            'dni.regex' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Ese DNI ya está asociado a una cuenta. Inicia sesión o recupera tu acceso.',
            'email.unique' => 'Ese correo ya está registrado. Inicia sesión o recupera tu acceso.',
            'privacy_accepted.accepted' => 'Debes aceptar el uso de tus datos descrito en el aviso de privacidad.',
        ]);
        unset($datos['privacy_accepted']);
        try {
            $user = User::create([...$datos, 'rol' => 'cliente', 'dni_consent_at' => now()]);
        } catch (UniqueConstraintViolationException $error) {
            throw ValidationException::withMessages(['dni' => 'No se pudo crear la cuenta porque el DNI o correo ya está registrado. Recupera tu acceso.']);
        }
        Auth::login($user);
        $request->session()->regenerate();
        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface $error) {
            report($error);

            return redirect()->route('verification.notice')->with('error', 'Tu cuenta se guardó, pero no pudimos enviar el correo. Usa el botón para reenviar el código.');
        }

        if (CorreoVerificacion::enModoPruebas()) {
            return redirect()->route('verification.notice')->with('error', CorreoVerificacion::NO_DISPONIBLE);
        }

        return redirect()->route('verification.notice')->with('success', 'Tu cuenta se creó. Verifica tu correo para realizar pedidos.');
    }

    public function sendResetLink(Request $request)
    {
        $this->normalizeEmail($request);
        $request->validate(['email' => ['required', 'email', 'max:255']]);
        Password::sendResetLink($request->only('email'));

        return back()->with('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $this->normalizeEmail($request);
        $datos = $request->validate(['token' => ['required', 'string'],
            'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()]]);
        $status = Password::reset($datos, function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace no es válido o ha expirado. Solicita uno nuevo.'])->onlyInput('email');
        }

        return redirect()->route('login')->with('success', 'Contraseña actualizada. Inicia sesión con tu nueva contraseña.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
    }

    private function loginAttemptKey(string $email): string
    {
        return 'login:failed:'.hash('sha256', strtolower($email));
    }
}
