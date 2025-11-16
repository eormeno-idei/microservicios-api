<?php

namespace App\Http\Middleware;

use App\Services\UI\Support\UIDebug;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Middleware DecryptUsimStorage
 *
 * Desencripta el contenido del header X-USIM-Storage,
 * lo convierte a un arreglo asociativo disponible como $request->storage
 * y, si existe una clave 'token', la inyecta como header
 * Authorization: Bearer <token> para compatibilidad con Laravel Sanctum.
 */
class DecryptUsimStorage
{
    public function handle(Request $request, Closure $next)
    {
        $storage = [];

        if ($request->hasHeader('X-USIM-Storage')) {
            $encrypted = $request->header('X-USIM-Storage');

            try {
                // Desencripta el contenido utilizando la APP_KEY del proyecto
                $decrypted = decrypt($encrypted);
                $storage = json_decode($decrypted, true);
            } catch (DecryptException $e) {
            }
        }

        // Si el contenido es válido, exponerlo y setear token Bearer
        if (is_array($storage)) {
            $request->merge(['storage' => $storage]);

            if (!empty($storage['store_token'])) {
                $request->headers->set('Authorization', 'Bearer ' . $storage['store_token']);
            }
        }

        return $next($request);
    }
}
