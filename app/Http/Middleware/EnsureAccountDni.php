<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountDni
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! preg_match('/^[0-9]{8}$/D', (string) $request->user()->dni)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Completa tu DNI en Mi cuenta antes de comprar.'], 403);
            }
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('profile')->with('error', 'Completa tu DNI en Mi cuenta antes de comprar.');
        }

        return $next($request);
    }
}
