<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogViewerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // En desarrollo, permitir acceso sin restricciones
        if (config('app.env') === 'local') {
            return $next($request);
        }

        // En producción, verificar autenticación
        if (!Auth::check()) {
            abort(403, 'Acceso no autorizado al visor de logs');
        }

        // Opcional: verificar roles o permisos específicos
        // if (!Auth::user()->hasRole('admin')) {
        //     abort(403, 'Solo administradores pueden acceder a los logs');
        // }

        // Opcional: verificar IPs permitidas
        $allowedIps = config('logging.viewer_allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            abort(403, 'Tu IP no está autorizada para acceder a los logs');
        }

        return $next($request);
    }
}
