<?php

declare(strict_types=1);

/**
 * Router para el servidor de desarrollo built-in de PHP.
 *
 * - Sirve archivos estáticos (CSS, JS, imágenes, fuentes) desde la raíz del proyecto.
 * - Delega cualquier otra petición a public/index.php (front controller).
 *
 * Esto resuelve el problema de que, con "-t public", los assets ubicados en
 * views/ no son accesibles porque el docroot queda restringido a public/.
 *
 * Uso:
 *   php -S localhost:6060 serve-router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (in_array($method, ['GET', 'HEAD'], true)) {
    $filePath = __DIR__ . $uri;

    if ($uri !== '/' && is_file($filePath)) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension !== 'php') {
            return false;
        }
    }
}

require __DIR__ . '/public/index.php';
