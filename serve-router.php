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

if (in_array($method, ['GET', 'HEAD'], true) && $uri !== '/' && is_file($filePath = __DIR__ . $uri)) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    if ($extension === 'php') {
        require $filePath;
        return true;
    }

    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'mjs'   => 'application/javascript',
        'json'  => 'application/json',
        'map'   => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'eot'   => 'application/vnd.ms-fontobject',
        'txt'   => 'text/plain',
        'xml'   => 'application/xml',
    ];

    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=3600');
    header('Connection: close');

    if ($method === 'HEAD') {
        return true;
    }

    readfile($filePath);
    return true;
}

require __DIR__ . '/public/index.php';
