<?php

/*=============================================
 * Depurar Errores
 * =============================================*/

define('DIR', __DIR__);

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', DIR . '/php_error_log');

/*=============================================
 * Requerimientos
 * =============================================*/

require_once DIR . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once 'controllers/template.controller.php';
require_once 'controllers/curl.controller.php';

/*=============================================
 * Plantilla
 * =============================================*/

$index = new TemplateController();
$index->index();

