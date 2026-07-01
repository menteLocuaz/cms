<?php

declare(strict_types=1);

use App\Controllers\TemplateController;
use Dotenv\Dotenv;

/**
 * Punto de entrada de la aplicación.
 *
 * @author CMS Builder
 */

define('BASE_PATH', dirname(__DIR__));

/*==================================================
=            Configuración de errores              =
==================================================*/

ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/php_error_log');

/*==================================================
=            Carga del autoloader                  =
==================================================*/

$autoload = BASE_PATH . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    exit(
        'No se encontró el archivo vendor/autoload.php. '
        . 'Ejecute primero "composer install".'
    );
}

require_once $autoload;

/*==================================================
=            Variables de entorno                  =
==================================================*/

try {
    Dotenv::createImmutable(BASE_PATH)->safeLoad();
} catch (Throwable $e) {
    error_log($e->getMessage());
}

/*==================================================
=            Inicio de la aplicación               =
==================================================*/

(new TemplateController())->index();
