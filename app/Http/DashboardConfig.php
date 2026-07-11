<?php

declare(strict_types=1);

namespace App\Http;

use App\Controllers\CurlController;

/**
 * Carga la configuración del dashboard (el registro admin principal)
 * desde la API remota, encapsulando todas las validaciones necesarias.
 */
final class DashboardConfig
{
    /**
     * Devuelve el objeto admin (con title_admin, font_admin, color_admin,
     * back_admin, symbol_admin, etc.) o null si la API no está disponible
     * o la base de datos aún no tiene tabla admins.
     */
    public static function load(): ?object
    {
        $response = CurlController::request('admins', 'GET', []);

        if (
            !is_object($response)
            || !isset($response->status)
            || $response->status !== 200
            || !isset($response->results[0])
            || !is_object($response->results[0])
        ) {
            return null;
        }

        return $response->results[0];
    }
}
