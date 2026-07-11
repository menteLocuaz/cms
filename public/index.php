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

$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

if ($appEnv === 'local' || $appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/php_error_log');

/*==================================================
 =            Carga del autoloader                  =
 ==================================================*/

$autoload = BASE_PATH . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    exit('No se encontró el archivo vendor/autoload.php. ' . 'Ejecute primero "composer install".');
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
