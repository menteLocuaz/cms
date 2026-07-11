<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\View\ViewRenderer;

/**
 * Punto de entrada del front controller.
 *
 * Tras el refactor de Fase 0.2, su única responsabilidad es despachar
 * al ViewRenderer, que se ocupa de aliasing y del render del layout.
 */
final class TemplateController
{
    public function index(): void
    {
        (new ViewRenderer())->render();
    }
}
