<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Constructor de URLs hacia la API remota y hacia rutas internas.
 *
 * Centraliza el patrón repetido:
 *   ?id=…&nameId=…&token=…&table=admins&suffix=admin
 *
 * Reduce errores y hace un solo punto donde añadir autenticación,
 * versionado, tracing, etc.
 */
final class UrlBuilder
{
    /**
     * Construye una URL para CurlController::request().
     * Devuelve solo el path con query string, sin base URL.
     */
    public static function api(string $path, array $params = []): string
    {
        return $path . ($params !== [] ? '?' . http_build_query($params) : '');
    }

    /**
     * Construye una URL completa a la API remota.
     */
    public static function apiFull(string $path, array $params = []): string
    {
        $base = $_ENV['API_BASE_URL']
            ?? $_ENV['CURL_API_URL']
            ?? getenv('API_BASE_URL')
            ?: getenv('CURL_API_URL')
            ?: 'http://localhost:9090/';

        return rtrim($base, '/') . '/' . ltrim($path, '/') . ($params !== [] ? '?' . http_build_query($params) : '');
    }

    /**
     * URL estándar para operaciones autenticadas con token de admin.
     */
    public static function adminAction(
        string $table,
        string $id,
        string $nameId = 'id',
        bool $withToken = true,
        ?string $token = null,
    ): string {
        $params = [
            'id' => $id,
            'nameId' => $nameId,
            'table' => 'admins',
            'suffix' => 'admin',
        ];

        if ($withToken) {
            $params['token'] = $token ?? ($_SESSION['admin']->token_admin ?? 'no');
        }

        return self::api($table, $params);
    }
}
