<?php

declare(strict_types=1);

namespace App\Http\View;

use App\Controllers\AdminsController;
use App\Controllers\CurlController;
use App\Controllers\DynamicController;
use App\Controllers\InstallController;
use App\Controllers\ModulesController;
use App\Controllers\PagesController;

/**
 * Encargado de preparar los alias de controllers sin namespace y de
 * delegar el render al layout principal.
 */
final class ViewRenderer
{
    private const ALIASES = [
        'CurlController'    => CurlController::class,
        'AdminsController'  => AdminsController::class,
        'PagesController'   => PagesController::class,
        'ModulesController' => ModulesController::class,
        'DynamicController' => DynamicController::class,
        'InstallController' => InstallController::class,
    ];

    public function render(): void
    {
        foreach (self::ALIASES as $alias => $class) {
            if (!class_exists($alias, false)) {
                class_alias($class, $alias);
            }
        }

        require BASE_PATH . '/views/template.php';
    }
}
