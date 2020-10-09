<?php

namespace App\Http\Middleware;

use Closure;

class PermisoConsultor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ((session()->get('rol_nombre') == ('consultor')) || (session()->get('rol_nombre') == ('supervisor')) || (session()->get('rol_nombre') == ('administrador')))
        return $next($request);
        return redirect('/tablero')->with('mensaje', 'No tiene permiso para entrar aqui');
    }

   
}
