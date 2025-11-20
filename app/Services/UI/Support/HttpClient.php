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
        return [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            'Authorization' => "Bearer " . UIStateManager::getAuthToken()
        ];
    }

    /**
     * Execute GET request
     */
    public static function get(string $route, array $queryParams = []): array
    {
        $url = route($route);

        $response = Http::withHeaders(self::getHeaders())
            ->get($url, $queryParams);

        return $response->json();
    }

    /**
     * Execute POST request
     */
    public static function post(string $route, array $data = []): array
    {
        $url = route($route);

        $response = Http::withHeaders(self::getHeaders())
            ->post($url, $data);

        return $response->json();
    }

    /**
     * Execute PUT request
     */
    public static function put(string $route, array $data = []): array
    {
        $url = route($route);

        $response = Http::withHeaders(self::getHeaders())
            ->put($url, $data);

        return $response->json();
    }

    /**
     * Execute PATCH request
     */
    public static function patch(string $route, array $data = []): array
    {
        $url = route($route);

        $response = Http::withHeaders(self::getHeaders())
            ->patch($url, $data);

        return $response->json();
    }

    /**
     * Execute DELETE request
     */
    public static function delete(string $route, array $data = []): array
    {
        $url = route($route);

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
