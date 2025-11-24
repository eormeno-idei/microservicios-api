<?php

namespace App\Services\UI\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

/**
 * HTTP Client for UI Services
 *
 * Provides authenticated HTTP methods for API communication
 */
class HttpClient
{
    /**
     * Get common headers with authentication
     */
    private static function getHeaders(): array
    {
        // Try to get token from current request or session
        $token = request()->bearerToken()
                 ?? session('auth_token')
                 ?? UIStateManager::getAuthToken();

        // Get client ID from cookie to maintain UI state
        $clientId = request()->cookie(UIStateManager::CLIENT_ID_COOKIE);

        $headers = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            'Authorization' => "Bearer " . $token
        ];

        // Pass client ID as cookie header
        if ($clientId) {
            $headers['Cookie'] = UIStateManager::CLIENT_ID_COOKIE . '=' . $clientId;
        }

        return $headers;
    }

    /**
     * Get cookie jar from current request
     */
    private static function getCookieJar(): \GuzzleHttp\Cookie\CookieJar
    {
        $cookies = new \GuzzleHttp\Cookie\CookieJar();

        // Copy all cookies from the current request
        foreach (request()->cookies as $name => $value) {
            $cookies->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                'Name' => $name,
                'Value' => $value,
                'Domain' => request()->getHost(),
                'Path' => '/',
            ]));
        }

        return $cookies;
    }

    /**
     * Execute GET request
     *
     * @param string $route Route name
     * @param array $queryParams Query parameters
     * @param array $routeParams Route parameters (e.g., ['user' => 123] for /users/{user})
     */
    public static function get(string $route, array $queryParams = [], array $routeParams = []): array
    {
        $url = route($route, $routeParams);

        $response = Http::withHeaders(self::getHeaders())
            ->get($url, $queryParams);

        return $response->json();
    }

    /**
     * Execute POST request
     *
     * @param string $route Route name
     * @param array $data Request body data
     * @param array $routeParams Route parameters (e.g., ['user' => 123] for /users/{user})
     */
    public static function post(string $route, array $data = [], array $routeParams = []): array
    {
        $url = route($route, $routeParams);

        $response = Http::withHeaders(self::getHeaders())
            ->post($url, $data);

        return $response->json();
    }

    /**
     * Execute PUT request
     *
     * @param string $route Route name
     * @param array $data Request body data
     * @param array $routeParams Route parameters (e.g., ['user' => 123] for /users/{user})
     */
    public static function put(string $route, array $data = [], array $routeParams = []): array
    {
        $url = route($route, $routeParams);

        $response = Http::withHeaders(self::getHeaders())
            ->put($url, $data);

        return $response->json();
    }

    /**
     * Execute PATCH request
     *
     * @param string $route Route name
     * @param array $data Request body data
     * @param array $routeParams Route parameters (e.g., ['user' => 123] for /users/{user})
     */
    public static function patch(string $route, array $data = [], array $routeParams = []): array
    {
        $url = route($route, $routeParams);

        $response = Http::withHeaders(self::getHeaders())
            ->patch($url, $data);

        return $response->json();
    }

    /**
     * Execute DELETE request
     *
     * @param string $route Route name
     * @param array $data Request body data (optional)
     * @param array $routeParams Route parameters (e.g., ['user' => 123] for /users/{user})
     */
    public static function delete(string $route, array $data = [], array $routeParams = []): array
    {
        $url = route($route, $routeParams);

        $response = Http::withHeaders(self::getHeaders())
            ->delete($url, $data);

        return $response->json();
    }

    /**
     * Execute request with custom method
     */
    public static function request(string $method, string $route, array $data = []): array
    {
        $url = route($route);

        $response = Http::withHeaders(self::getHeaders())
            ->send($method, $url, ['json' => $data]);

        return $response->json();
    }

    /**
     * Get raw Response object for GET request
     */
    public static function getRaw(string $route, array $queryParams = []): Response
    {
        $url = route($route);

        return Http::withHeaders(self::getHeaders())
            ->get($url, $queryParams);
    }

    /**
     * Get raw Response object for POST request
     */
    public static function postRaw(string $route, array $data = []): Response
    {
        $url = route($route);

        return Http::withHeaders(self::getHeaders())
            ->post($url, $data);
    }
}
