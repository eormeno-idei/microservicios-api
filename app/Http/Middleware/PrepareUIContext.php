<?php

namespace App\Http\Middleware;

use Idei\Usim\Services\Support\UIDebug;
use Idei\Usim\Services\Support\UIStateManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Middleware PrepareUIContext
 *
 * Prepara el contexto del request para el framework UI:
 * 1. Desencripta el contenido del header X-USIM-Storage
 * 2. Inyecta route params desde query params con prefijo "route_"
 * 3. Configura autenticación Bearer si existe token
 */
class PrepareUIContext
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Desencriptar USIM Storage
        $this->decryptUsimStorage($request);

        // 2. Inyectar route params desde query
        $this->injectRouteParamsFromQuery($request);

        return $next($request);
    }

    /**
     * Desencripta el contenido del header X-USIM-Storage
     * y lo inyecta como $request->storage
     */
    private function decryptUsimStorage(Request $request): void
    {
        $storage = [];
        $encrypted = null;

        // 1. Intentar obtener desde Header (y verificar que no esté vacío)
        if ($request->hasHeader('X-USIM-Storage')) {
            $headerValue = $request->header('X-USIM-Storage');
            if (!empty($headerValue) && $headerValue !== 'null' && $headerValue !== 'undefined') {
                $encrypted = $headerValue;
            }
        }

        // 2. Si no hay header válido, intentar desde Input 'usim'
        if (empty($encrypted) && $request->has('usim')) {
            $encrypted = $request->input('usim');
        }

        if ($encrypted) {
            try {
                // Desencripta el contenido utilizando la APP_KEY del proyecto
                $decrypted = decrypt($encrypted);
                $storage = json_decode($decrypted, true);
                \Illuminate\Support\Facades\Log::info('UIContext Decrypted:', ['keys' => array_keys($storage ?? [])]);

            } catch (DecryptException $e) {
                \Illuminate\Support\Facades\Log::warning('UIContext Decrypt Failed: ' . $e->getMessage());
                // Silently fail - storage will be empty
            }
        }

        // Si el contenido es válido, exponerlo y setear token Bearer
        if (is_array($storage)) {
            $request->merge(['storage' => $storage]);

            if (!empty($storage['store_token'])) {
                $store_token = $storage['store_token'];
                $request->headers->set('Authorization', 'Bearer ' . $store_token);
                UIStateManager::setAuthToken($store_token);
            }
        }
    }

    /**
     * Inyecta query params con prefijo "route_" como route parameters
     *
     * Ejemplo: ?route_id=123&route_hash=abc
     * Resultado: request()->route('id') = "123", request()->route('hash') = "abc"
     */
    private function injectRouteParamsFromQuery(Request $request): void
    {
        $route = $request->route();

        if (!$route) {
            return; // No hay ruta, salir
        }

        $queryParams = $request->query();

        foreach ($queryParams as $key => $value) {
            // Solo procesar params que empiecen con "route_"
            if (strpos($key, 'route_') === 0) {
                // Extraer el nombre real del parámetro (sin el prefijo)
                $paramName = substr($key, 6); // Quitar "route_"

                // Solo inyectar si NO existe ya un route param real con ese nombre
                // (los route params reales tienen precedencia)
                if (!$route->hasParameter($paramName)) {
                    $route->setParameter($paramName, $value);
                }

                // Opcional: Remover del query string para limpiar
                $request->query->remove($key);
            }
        }
    }
}
