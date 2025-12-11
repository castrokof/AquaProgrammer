<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        // Buscar usuario por token hasheado
        $hashedToken = hash('sha256', $token);
        $user = Usuario::where('api_token', $hashedToken)
                    ->where('estado', 'activo')
                    ->first();

        if (!$user) {
            return response()->json(['error' => 'Token inválido o usuario inactivo'], 401);
        }

        // Autenticar al usuario
        Auth::setUser($user);

        return $next($request);
    }
}