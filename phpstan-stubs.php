<?php

/**
 * PHPStan bootstrap.
 *
 * Define runtime constants that are normally provided by the front
 * controller (public/index.php) but are not visible to the static
 * analyzer when it scans the rest of the codebase in isolation.
 */

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
defined('DIR') || define('DIR', BASE_PATH);
