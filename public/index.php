<?php

declare(strict_types=1);

use App\Controllers\TemplateController;
use Dotenv\Dotenv;

define('BASE_PATH', dirname(__DIR__));

/**
 * Punto de entrada de la aplicación.
 *
 * @author CMS Builder
 */

/*==================================================
=            Carga del autoloader                  =
==================================================*/

$autoload = BASE_PATH . '/vendor/autoload.php';

if (!is_file($autoload)) {
    exit(
        'No se encontró el archivo "vendor/autoload.php". '
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
=            Configuración de errores              =
==================================================*/

configureErrorHandling();

/*==================================================
=            Inicio de la aplicación               =
==================================================*/

(new TemplateController())->index();

/**
 * Configura el manejo de errores según el entorno.
 */
function configureErrorHandling(): void
{
    $environment = $_ENV['APP_ENV']
        ?? getenv('APP_ENV')
        ?: 'production';

    $isDevelopment = in_array(
        $environment,
        ['local', 'development'],
        true
    );

    ini_set('display_errors', $isDevelopment ? '1' : '0');
    ini_set('display_startup_errors', $isDevelopment ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/php_error_log');

    error_reporting(
        $isDevelopment
            ? E_ALL
            : E_ALL & ~E_DEPRECATED & ~E_STRICT
    );
}
