<?php

namespace App\Http\Middleware;

use Closure;

class PermisoEditor
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
        if ((session()->get('rol_nombre') == ('administrador')) || (session()->get('rol_nombre') == ('supervisor')))
        return $next($request);
        
        return redirect('/tablero')->with('mensaje', 'No tienes autorización para realizar esta acción.');
    }
}
